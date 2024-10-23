<?php

namespace Modules\SystemConfiguration\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Services\DemoModeService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Authentication\Models\User;
use Modules\SystemConfiguration\Http\Requests\Settings\SettingUpdateRequest;
use Modules\SystemConfiguration\Models\Setting;
use RealRashid\SweetAlert\Facades\Alert;

class UtilityController extends Controller
{
    public function settings(SettingUpdateRequest $request, $store = ''): Factory|RedirectResponse|View
    {
        if ($store === '') {
            return view('systemconfiguration::general_settings.settings');
        }

        app(DemoModeService::class)->check('system_settings');

        DB::beginTransaction();

        try {

            foreach ($request->validated() as $key => $value) {

                Setting::updateOrCreate(
                    [
                        'institute_id' => get_institute_id(),
                        'branch_id' => get_branch_id(),
                        'name' => $key,
                    ],
                    [
                        'type' => 'general',
                        'value' => $value,
                    ]
                );
            }

            DB::commit();

            Alert::success('Success', 'Settings updated successfully.');

            return redirect()->back();
        } catch (\Throwable $e) {

            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return redirect()->back()->withInput();
        }
    }

    public function change_session($session_id): Redirector|RedirectResponse
    {
        if ($session_id != '') {
            Setting::updateOrCreate(
                [
                    'institute_id' => get_institute_id(),
                    'branch_id' => get_branch_id(),
                    'name' => 'academic_year',
                ],
                [
                    'type' => 'general',
                    'value' => $session_id,
                ]
            );
        }

        return redirect($_SERVER['HTTP_REFERER'])->with('success', _lang('Session Changed Successfully.'));
    }

    public function upload_file($file_name, Request $request): void
    {

        if ($request->hasFile($file_name)) {
            $file = $request->file($file_name);
            $name = 'file_'.time().'.'.$file->getClientOriginalExtension();
            $destinationPath = public_path('/uploads/media');
            $file->move($destinationPath, $name);

            Setting::updateOrCreate(
                [
                    'institute_id' => get_institute_id(),
                    'branch_id' => get_branch_id(),
                    'name' => $file_name,
                ],
                [
                    'type' => 'general',
                    'value' => $name,
                ]
            );
        }
    }

    public function backupDatabase()
    {
        // A raw SQL dump necessarily contains every tenant's data — restrict it to a
        // platform-level SAAS Admin rather than any authenticated institute user.
        abort_unless(optional(auth()->user())->user_type === 'SAAS Admin', 403, 'Only a SAAS Admin can export a database backup.');

        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        $return = '';
        $database = 'Tables_in_'.DB::getDatabaseName();
        $tables = [];
        $result = DB::select('SHOW TABLES');

        foreach ($result as $table) {
            $tables[] = $table->$database;
        }

        // loop through the tables
        foreach ($tables as $table) {
            $return .= "DROP TABLE IF EXISTS $table;";
            $result2 = DB::select("SHOW CREATE TABLE $table");
            $row2 = $result2[0]->{'Create Table'};
            $return .= "\n\n".$row2.";\n\n";
            $result = DB::select("SELECT * FROM $table");

            foreach ($result as $row) {
                $return .= "INSERT INTO $table VALUES(";
                foreach ($row as $key => $val) {
                    $return .= "'".addslashes($val)."',";
                }
                $return = substr_replace($return, '', -1);
                $return .= ");\n";
            }

            $return .= "\n\n\n";
        }

        // Save outside the public webroot so the dump can never be fetched directly by URL.
        $directory = storage_path('app/backups');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $file = $directory.DIRECTORY_SEPARATOR.'DB-BACKUP-'.now()->format('Y-m-d_His').'-'.Str::random(12).'.sql';
        file_put_contents($file, $return);

        return response()->download($file)->deleteFileAfterSend(true);
    }

    public function change_branch($branch_id): Redirector|RedirectResponse
    {
        if ($branch_id != '') {
            User::where('id', auth()->user()->id)->update([
                'branch_id' => $branch_id,
            ]);
        }

        return redirect($_SERVER['HTTP_REFERER'])->with('success', _lang('Branch Changed Successfully.'));
    }
}
