<?php

namespace App\Console\Commands;

use App\Http\Middleware\EnsureAttendanceDevice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Authentication\Models\Institute;

class AttendanceDeviceToken extends Command
{
    protected $signature = 'attendance:device-token
                            {institute? : Institute id. Omit to list every institute}
                            {--regenerate : Replace an existing token (immediately invalidates the old one)}';

    protected $description = 'Show or mint the biometric attendance device token for an institute';

    public function handle(): int
    {
        $instituteId = $this->argument('institute');

        if ($instituteId === null) {
            return $this->listAll();
        }

        $institute = Institute::whereKey((int) $instituteId)->first(['id', 'name']);

        if (! $institute) {
            $this->error("No institute with id {$instituteId}.");

            return self::FAILURE;
        }

        $existing = DB::table('settings')
            ->where('institute_id', $institute->id)
            ->where('name', EnsureAttendanceDevice::SETTING_NAME)
            ->first(['id', 'value']);

        if ($existing && ! $this->option('regenerate')) {
            $this->info("Institute {$institute->id} ({$institute->name}) device token:");
            $this->line('  '.$existing->value);
            $this->comment('Pass --regenerate to replace it. Any device still using the old token will stop working immediately.');

            return self::SUCCESS;
        }

        $token = bin2hex(random_bytes(32));

        if ($existing) {
            DB::table('settings')->where('id', $existing->id)->update([
                'value' => $token,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('settings')->insert([
                'institute_id' => $institute->id,
                // Device tokens are institute-wide: a school's readers are shared
                // across branches, and the middleware resolves the branch from the
                // row it matches. Left null so it is not tied to one branch.
                'branch_id' => null,
                'name' => EnsureAttendanceDevice::SETTING_NAME,
                'value' => $token,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // get_option() serves from a per-process map; this write bypassed Eloquent.
        clear_settings_map();

        $this->info(($existing ? 'Regenerated' : 'Minted')." device token for institute {$institute->id} ({$institute->name}):");
        $this->line('  '.$token);
        $this->newLine();
        $this->comment('Configure the device to send it as an X-Device-Token header on POST /api/student-device-attendance.');

        return self::SUCCESS;
    }

    private function listAll(): int
    {
        $tokens = DB::table('settings')
            ->where('name', EnsureAttendanceDevice::SETTING_NAME)
            ->pluck('value', 'institute_id');

        $rows = Institute::orderBy('id')->get(['id', 'name'])->map(fn ($institute) => [
            $institute->id,
            $institute->name,
            isset($tokens[$institute->id]) ? 'yes' : 'NO TOKEN',
        ])->all();

        if ($rows === []) {
            $this->warn('No institutes found.');

            return self::SUCCESS;
        }

        $this->table(['Institute', 'Name', 'Device token'], $rows);
        $this->comment('Mint one with: php artisan attendance:device-token {institute_id}');

        return self::SUCCESS;
    }
}
