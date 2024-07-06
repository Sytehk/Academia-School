<?php

namespace Modules\Authentication\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Academic\Database\Seeders\WhatsAppTemplateSeeder;
use Modules\Authentication\Models\Branch;
use Modules\Authentication\Models\Currency;
use Modules\Authentication\Models\Institute;
use Modules\Frontend\Models\AboutUs;
use Modules\Frontend\Models\HomeSection;
use Modules\Frontend\Models\Policy;
use RuntimeException;
use Throwable;

/**
 * Seeds or clones tenant configuration for a newly created school.
 *
 * Operational data (students, invoices, exam marks, attendance) is never copied.
 * Only setup rows — settings, grade scales, policies, and public CMS content —
 * are remapped onto the new institute_id / branch_id.
 */
class InstituteDefaultSetupService
{
    /**
     * Old id -> new id, keyed by table, for tables other cloned rows reference by foreign key.
     * Reset at the start of each cloneFromTemplate() call.
     *
     * @var array<string, array<int, int>>
     */
    private array $idMaps = [];

    /**
     * Tables copied from a template school. Each entry is institute-scoped
     * configuration, not live academic or finance records.
     *
     * @var list<array{table: string, institute_only?: bool, type?: string}>
     */
    private const CLONE_TABLES = [
        ['table' => 'shifts'],
        ['table' => 'classes', 'produces_map' => true],
        ['table' => 'student_groups', 'produces_map' => true],
        ['table' => 'sections', 'remap' => [
            'class_id' => ['table' => 'classes', 'required' => true],
            'student_group_id' => ['table' => 'student_groups', 'required' => false],
        ]],
        ['table' => 'subjects', 'remap' => [
            'class_id' => ['table' => 'classes', 'required' => true],
            'group_id' => ['table' => 'student_groups', 'required' => false],
        ]],
        ['table' => 'settings', 'type' => 'general'],
        ['table' => 'grades'],
        ['table' => 'policies'],
        ['table' => 'about_us'],
        ['table' => 'home_sections'],
        ['table' => 'why_choose_us'],
        ['table' => 'institute_image_settings', 'institute_only' => true],
        ['table' => 'whats_app_templates'],
        ['table' => 'sms_templates'],
        ['table' => 'pages'],
        ['table' => 'banners'],
        ['table' => 'ready_to_join_us'],
        ['table' => 'mobile_app_sections'],
        ['table' => 'benefits'],
        ['table' => 'achievements'],
        ['table' => 'faq_questions'],
        ['table' => 'our_histories'],
        ['table' => 'testimonials'],
    ];

    /**
     * Apply a template school when one is selected, otherwise seed built-in defaults.
     *
     * @return array{mode: string, template_institute_id: int|null, copied: array<string, int>}
     */
    public function apply(Institute $institute, Branch $branch, ?int $templateInstituteId): array
    {
        if ($templateInstituteId) {
            return $this->cloneFromTemplate($templateInstituteId, $institute, $branch);
        }

        $this->seedDefaults($institute, $branch);

        return [
            'mode' => 'defaults',
            'template_institute_id' => null,
            'copied' => [],
        ];
    }

    public function seedDefaults(Institute $institute, Branch $branch): void
    {
        $this->seedInstituteSettings($institute, $branch);
        $this->seedHomeSections($institute, $branch);
        Policy::ensureDefaultsFor((int) $institute->id, (int) $branch->id);
        $this->seedAboutUs($institute, $branch);
        $this->seedGrades($institute, $branch);
        WhatsAppTemplateSeeder::seedFor((int) $institute->id, (int) $branch->id);
    }

    /**
     * @return array{mode: string, template_institute_id: int, copied: array<string, int>}
     */
    public function cloneFromTemplate(int $templateInstituteId, Institute $institute, Branch $branch): array
    {
        if ($templateInstituteId === (int) $institute->id) {
            throw new RuntimeException('A school cannot use itself as the default setup template.');
        }

        $source = Institute::query()->find($templateInstituteId);

        if (! $source) {
            throw new RuntimeException('The selected default school setup no longer exists.');
        }

        $sourceBranch = Branch::query()
            ->where('institute_id', $source->id)
            ->orderBy('id')
            ->first();

        if (! $sourceBranch) {
            throw new RuntimeException('The selected default school has no branch to copy from.');
        }

        $copied = [];
        $this->idMaps = [];

        try {
            foreach (self::CLONE_TABLES as $definition) {
                $copied[$definition['table']] = $this->copyTable(
                    $definition,
                    (int) $source->id,
                    (int) $sourceBranch->id,
                    $institute,
                    $branch
                );
            }

            if (($copied['settings'] ?? 0) === 0) {
                $this->seedInstituteSettings($institute, $branch);
            } else {
                $this->overrideIdentitySettings($institute, $branch);
            }

            if (($copied['home_sections'] ?? 0) === 0) {
                $this->seedHomeSections($institute, $branch);
            }

            if (! empty($source->theme_id)) {
                $institute->forceFill(['theme_id' => $source->theme_id])->save();
            }

            Policy::ensureDefaultsFor((int) $institute->id, (int) $branch->id);

            if (! $this->hasRows('grades', (int) $institute->id)) {
                $this->seedGrades($institute, $branch);
            }

            if (! $this->hasRows('about_us', (int) $institute->id)) {
                $this->seedAboutUs($institute, $branch);
            }

            WhatsAppTemplateSeeder::seedFor((int) $institute->id, (int) $branch->id);
        } catch (Throwable $e) {
            Log::error('Default school setup clone failed', [
                'template_institute_id' => $templateInstituteId,
                'target_institute_id' => $institute->id,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException(
                'Failed to copy the default school setup: '.$e->getMessage(),
                0,
                $e
            );
        }

        return [
            'mode' => 'template',
            'template_institute_id' => $templateInstituteId,
            'copied' => $copied,
        ];
    }

    /**
     * @param  array{table: string, institute_only?: bool, type?: string}  $definition
     */
    protected function copyTable(
        array $definition,
        int $sourceInstituteId,
        int $sourceBranchId,
        Institute $target,
        Branch $targetBranch
    ): int {
        $table = $definition['table'];

        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'institute_id')) {
            return 0;
        }

        $query = DB::table($table)->where('institute_id', $sourceInstituteId);

        if (! empty($definition['type']) && Schema::hasColumn($table, 'type')) {
            $query->where('type', $definition['type']);
        }

        if (empty($definition['institute_only']) && Schema::hasColumn($table, 'branch_id')) {
            $query->where(function ($inner) use ($sourceBranchId) {
                $inner->where('branch_id', $sourceBranchId)->orWhereNull('branch_id');
            });
        }

        if (Schema::hasColumn($table, 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        $rows = $query->get();
        if ($rows->isEmpty()) {
            return 0;
        }

        $now = Carbon::now();
        $remap = $definition['remap'] ?? [];
        $producesMap = ! empty($definition['produces_map']);
        $inserts = [];
        $sourceIds = [];

        foreach ($rows as $row) {
            $data = (array) $row;
            $oldId = $data['id'];
            unset($data['id']);

            $data['institute_id'] = $target->id;

            if (array_key_exists('branch_id', $data)) {
                $data['branch_id'] = $targetBranch->id;
            }

            if (array_key_exists('created_by', $data)) {
                $data['created_by'] = null;
            }

            if (array_key_exists('session_id', $data)) {
                $data['session_id'] = null;
            }

            if (array_key_exists('created_at', $data)) {
                $data['created_at'] = $now;
            }

            if (array_key_exists('updated_at', $data)) {
                $data['updated_at'] = $now;
            }

            $skip = false;

            foreach ($remap as $column => $spec) {
                if (! array_key_exists($column, $data)) {
                    continue;
                }

                $oldRef = $data[$column];
                $mapped = $oldRef !== null ? ($this->idMaps[$spec['table']][$oldRef] ?? null) : null;

                if ($mapped === null && ! empty($spec['required'])) {
                    $skip = true;

                    break;
                }

                $data[$column] = $mapped;
            }

            if ($skip) {
                continue;
            }

            $inserts[] = $data;
            $sourceIds[] = $oldId;
        }

        if ($inserts === []) {
            return 0;
        }

        if ($producesMap) {
            $this->idMaps[$table] ??= [];

            foreach ($inserts as $index => $data) {
                $newId = DB::table($table)->insertGetId($data);
                $this->idMaps[$table][$sourceIds[$index]] = $newId;
            }
        } else {
            foreach (array_chunk($inserts, 100) as $chunk) {
                DB::table($table)->insert($chunk);
            }
        }

        return count($inserts);
    }

    protected function overrideIdentitySettings(Institute $institute, Branch $branch): void
    {
        $overrides = [
            'school_name' => $institute->name,
            'site_title' => $institute->name,
            'email' => $institute->email,
            'phone' => $institute->phone,
            'address' => $institute->address,
            'logo' => $institute->logo,
        ];

        foreach ($overrides as $name => $value) {
            if (blank($value)) {
                continue;
            }

            DB::table('settings')
                ->where('institute_id', $institute->id)
                ->where('branch_id', $branch->id)
                ->where('name', $name)
                ->update([
                    'value' => (string) $value,
                    'updated_at' => Carbon::now(),
                ]);
        }
    }

    protected function seedInstituteSettings(Institute $institute, Branch $branch): void
    {
        $config = config('onboarding.institute_settings');
        $values = $config['defaults'];

        foreach ($config['derived_from_institute'] as $settingKey => $instituteAttribute) {
            $derived = $institute->{$instituteAttribute};

            if (! blank($derived)) {
                $values[$settingKey] = $derived;
            }
        }

        if (blank($values['copyright_text'])) {
            $values['copyright_text'] = '&copy; Copyright '.Carbon::now()->year
                .'. All Rights Reserved by '.config('app.name');
        }

        // Resolved by code, not a literal id, so reseeding/reordering the
        // currencies table can't silently hand new institutes the wrong one.
        $defaultCurrencyId = Currency::where('code', config('onboarding.default_currency_code'))
            ->where('status', 1)
            ->value('id');
        if ($defaultCurrencyId) {
            $values['currency_id'] = $defaultCurrencyId;
        }

        $now = Carbon::now();
        $rows = [];

        foreach ($values as $name => $value) {
            $rows[] = [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => $config['type'],
                'name' => $name,
                'value' => (string) $value,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('settings')->insert($rows);
    }

    protected function seedHomeSections(Institute $institute, Branch $branch): void
    {
        $now = Carbon::now();
        $rows = [];

        foreach (array_values(config('onboarding.home_sections', [])) as $index => $section) {
            $rows[] = [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'title' => $section['title'],
                'description' => $section['description'],
                'serial' => $index + 1,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            HomeSection::insert($rows);
        }
    }

    protected function hasRows(string $table, int $instituteId): bool
    {
        if (! Schema::hasTable($table)) {
            return false;
        }

        $query = DB::table($table)->where('institute_id', $instituteId);

        if (Schema::hasColumn($table, 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        return $query->exists();
    }

    protected function seedAboutUs(Institute $institute, Branch $branch): void
    {
        if ($this->hasRows('about_us', (int) $institute->id)) {
            return;
        }

        AboutUs::query()->create([
            'institute_id' => $institute->id,
            'branch_id' => $branch->id,
            'title' => 'About '.$institute->name,
            'description' => 'Learn more about '.$institute->name.', our mission, and the community we serve.',
            'long_description' => '<p>Welcome to '.e($institute->name).'.</p><p>We are committed to academic excellence, character development, and a supportive learning environment for every student.</p>',
            'status' => 1,
        ]);
    }

    protected function seedGrades(Institute $institute, Branch $branch): void
    {
        if ($this->hasRows('grades', (int) $institute->id)) {
            return;
        }

        $now = Carbon::now();
        $rows = [];

        foreach (config('onboarding.default_grades', []) as $grade) {
            $rows[] = [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'created_at' => $now,
                'updated_at' => $now,
                ...$grade,
            ];
        }

        if ($rows !== []) {
            DB::table('grades')->insert($rows);
        }
    }
}
