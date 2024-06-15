<?php

use App\Enums\VoucherType;
use App\Services\DemoModeService;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Academic\Models\Event;
use Modules\Academic\Models\Notice;
use Modules\Academic\Models\Signature;
use Modules\Academic\Models\WhatsAppSetting;
use Modules\Authentication\Models\User;
use Modules\Authentication\Services\SubscriptionLimitService;
use Modules\SystemConfiguration\Models\Setting;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

if (! function_exists('is_demo_mode')) {
    function is_demo_mode(): bool
    {
        try {
            return app(DemoModeService::class)->enabled();
        } catch (Throwable) {
            return (bool) config('system.demo.enabled', config('app.demo_mode', false));
        }
    }
}

if (! function_exists('can_access_system')) {
    function can_access_system(string $permission): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        try {
            if ($user->can($permission)) {
                return true;
            }
        } catch (Throwable) {
            // Permission may not exist yet on older databases.
        }

        // Every permission is registered on the `web` guard, but an API request
        // (auth:api) makes `api` the default guard, so can() above looks for an
        // `api` permission that never exists and a correctly-granted staff role
        // is refused. Check the web-guard grant explicitly.
        try {
            if ($user->hasPermissionTo($permission, 'web')) {
                return true;
            }
        } catch (Throwable) {
            // Unknown permission.
        }

        return in_array($user->user_type, ['Admin', 'SAAS Admin'], true);
    }
}

// Get the function for user Institute ID
if (! function_exists('get_institute_id')) {
    function get_institute_id()
    {
        return auth()->check() ? auth()->user()->institute_id : null;
    }
}

// Get the function for user Branch ID
if (! function_exists('get_branch_id')) {
    function get_branch_id()
    {
        return auth()->check() ? auth()->user()->branch_id : null;
    }
}

if (! function_exists('plan_can_create')) {
    /**
     * Whether the logged-in user's institute may create $count more of a plan-limited
     * resource (e.g. 'branch', 'student'). Use in Blade to hide "Add" buttons when the
     * plan limit is reached. Returns true when there is no institute/plan context.
     */
    function plan_can_create(string $resource, int $count = 1): bool
    {
        $institute = auth()->user()?->institute ?? null;
        if (! $institute) {
            return true;
        }

        return app(SubscriptionLimitService::class)
            ->canCreate($institute, $resource, $count);
    }
}

if (! function_exists('plan_remaining')) {
    /**
     * Remaining allowance for a plan-limited resource, or null when unlimited / no plan.
     */
    function plan_remaining(string $resource): ?int
    {
        $institute = auth()->user()?->institute ?? null;
        if (! $institute) {
            return null;
        }

        return app(SubscriptionLimitService::class)
            ->remaining($institute, $resource);
    }
}

if (! function_exists('qr_code_data_uri')) {
    /**
     * Base64 SVG data URI for a QR code, memoised per (value, size) for the
     * life of the request.
     *
     * Every ID card renders id-card/qr.blade.php TWICE with the SAME value
     * (once on the card front, once on the back) -- a static declared
     * directly inside a Blade @php block does NOT achieve this: Laravel's
     * PhpEngine renders a compiled view via require (not require_once, since
     * the same view legitimately needs to render again with different data),
     * and a plain `static $x` at a script's top level is reinitialised on
     * every fresh require of that file -- confirmed by a timing test that
     * showed no speedup on a second render before this was moved here. A
     * genuine function declaration (guarded by function_exists, so it is only
     * ever defined once) is what actually persists a static across calls.
     */
    function qr_code_data_uri(string $value, int $size): string
    {
        static $cache = [];

        $payload = trim($value) !== '' ? $value : 'N/A';
        $key = $payload.'|'.$size;

        if (! isset($cache[$key])) {
            $svg = (string) QrCode::size($size)
                ->margin(0)
                ->errorCorrection('M')
                ->generate($payload);

            $cache[$key] = base64_encode($svg);
        }

        return $cache[$key];
    }
}

if (! function_exists('_lang')) {
    /**
     * Translate a UI string.
     *
     * The translation catalogue is read from disk and decoded ONCE per locale per
     * process, not once per call. Previously every one of this app's ~5,700 call
     * sites re-ran file_get_contents() + json_decode() over lang/en.json (51 KB)
     * or lang/bn.json (79 KB); a page with ~200 translated strings therefore read
     * and parsed ~16 MB before rendering any content.
     *
     * The locale itself is still resolved per call (two array lookups now that
     * get_option() is memoised) rather than cached in a static, so a language
     * switch inside the same process is picked up immediately.
     */
    function _lang($string = '')
    {
        /** @var array<string, array<string, string>> $catalogues keyed by locale */
        static $catalogues = [];

        $sessionLocale = Session::get('locale');
        $targetLang = ! empty($sessionLocale)
            ? $sessionLocale
            : (get_option('language') ?: 'en');

        if (! isset($catalogues[$targetLang])) {
            $path = lang_path()."/$targetLang.json";

            $catalogues[$targetLang] = is_file($path)
                ? (json_decode((string) file_get_contents($path), true) ?: [])
                : [];
        }

        return ! empty($catalogues[$targetLang][$string])
            ? $catalogues[$targetLang][$string]
            : $string;
    }
}

if (! function_exists('load_language')) {
    function load_language($active = '')
    {
        $path = lang_path();
        $files = scandir($path);
        $options = '';

        foreach ($files as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            if ($name == '.' || $name == '' || $name == 'language') {
                continue;
            }

            $selected = '';
            if ($active == $name) {
                $selected = 'selected';
            } else {
                $selected = '';
            }

            $options .= "<option value='$name' $selected>".ucwords($name).'</option>';
        }
        echo $options;
    }
}

if (! function_exists('startsWith')) {
    function startsWith($haystack, $needle)
    {
        $length = strlen($needle);

        return substr($haystack, 0, $length) === $needle;
    }
}

if (! function_exists('create_option')) {
    function create_option($table, $value, $display, $selected = '', $where = [], $type = null)
    {
        $query = DB::table($table);

        // Apply institute and branch filter only if columns exist
        $columns = Schema::getColumnListing($table);

        if (in_array('institute_id', $columns)) {
            $query->where('institute_id', get_institute_id());
        }

        if (in_array('branch_id', $columns)) {
            $query->where('branch_id', get_branch_id());
        }

        // Dynamic where conditions
        foreach ($where as $key => $val) {
            $query->where($key, $val);
        }

        $rows = $query->get();

        $options = '';

        foreach ($rows as $row) {

            if ($type == 'subject') {

                $label = ucwords($row->$display)
                    .'-'.($row->subject_code ?? '');
            } else {

                $label = ucwords($row->$display);
            }

            $selectedText = ($selected != '' && $selected == $row->$value)
                ? 'selected'
                : '';

            $options .= '<option value="'.$row->$value.'" '.$selectedText.'>'
                .$label.
                '</option>';
        }

        return $options;
    }
}

if (! function_exists('get_table')) {

    function get_table(string $table, $where = [])
    {
        $query = DB::table($table);

        // Dynamic institute filter
        $columns = Schema::getColumnListing($table);

        if (in_array('institute_id', $columns)) {
            $query->where('institute_id', get_institute_id());
        }

        if (in_array('branch_id', $columns)) {
            $query->where('branch_id', get_branch_id());
        }

        // Custom where conditions
        if (! empty($where)) {
            foreach ($where as $key => $value) {
                $query->where($key, $value);
            }
        }

        return $query->get();
    }
}

if (! function_exists('get_logo_file_path')) {
    function get_logo_file_path()
    {
        $logo = get_option('logo');

        if ($logo == '') {
            return 'storage/logos/logo.png';
        }

        return "storage/logos/$logo";
    }
}

if (! function_exists('get_logo')) {
    function get_logo()
    {
        return asset(get_logo_file_path());
    }
}

if (! function_exists('sql_escape')) {
    function sql_escape($unsafe_str)
    {

        $unsafe_str = stripslashes($unsafe_str);

        return $escaped_str = str_replace("'", '', $unsafe_str);
    }
}

if (! function_exists('settings_map')) {
    /**
     * Every settings row for the acting tenant, as name => value, resolved once
     * per tenant per process.
     *
     * get_option() used to issue three round-trips on EVERY call: a PDO liveness
     * check, an uncached Schema::hasTable() against information_schema, and the
     * SELECT itself. With 400+ direct call sites — and _lang() multiplying that by
     * the number of translated strings on the page — this was the single largest
     * source of query volume in the application.
     *
     * The where() semantics deliberately match the original per-call query:
     * Illuminate's Builder::where($column, null) compiles to "IS NULL", so an
     * unauthenticated caller resolves NULL-tenant rows exactly as it did before.
     *
     * @return array<string, string|null>
     */
    function settings_map(bool $flush = false): array
    {
        static $maps = [];
        static $tableExists = false;

        if ($flush) {
            $maps = [];

            return [];
        }

        try {
            // Resolving the tenant touches the auth guard, which can itself blow up
            // very early in the boot cycle — the original get_option() had this
            // inside its try/catch, so keep it there.
            $instituteId = get_institute_id();
            $branchId = get_branch_id();
            $key = ($instituteId ?? 'null').'|'.($branchId ?? 'null');

            if (array_key_exists($key, $maps)) {
                return $maps[$key];
            }

            // Only the positive result is memoised. During installation the table
            // can appear part-way through a process, so a negative answer is
            // re-checked rather than cached — same cost as the old behaviour, and
            // only on systems that have not been installed yet.
            if (! $tableExists) {
                DB::connection()->getPdo();

                if (! Schema::hasTable('settings')) {
                    return [];
                }

                $tableExists = true;
            }

            return $maps[$key] = DB::table('settings')
                ->where('institute_id', $instituteId)
                ->where('branch_id', $branchId)
                ->pluck('value', 'name')
                ->all();
        } catch (Throwable) {
            // A dead connection must not be memoised as "this tenant has no
            // settings" — return empty for this call and retry on the next.
            return [];
        }
    }
}

if (! function_exists('clear_settings_map')) {
    /**
     * Drop the per-process settings memo so the next get_option() re-reads from
     * the database. Called automatically whenever a Setting row is written (see
     * AppServiceProvider::boot()); call it directly after a raw DB::table('settings')
     * write that must be visible within the same request.
     */
    function clear_settings_map(): void
    {
        settings_map(true);
    }
}

if (! function_exists('get_option')) {
    function get_option($name)
    {
        $map = settings_map();

        return $map[$name] ?? '';
    }
}

if (! function_exists('get_saas_option')) {
    function get_saas_option(string $name, $default = '')
    {
        try {
            // Check DB connection
            DB::connection()->getPdo();

            // Ensure table exists
            if (! Schema::hasTable('s_a_a_s_settings')) {
                return $default;
            }

            // Fetch setting
            $setting = DB::table('s_a_a_s_settings')
                ->where('name', $name)
                ->first();

            return $setting ? $setting->value : $default;
        } catch (Throwable $e) {
            return $default;
        }
    }
}

if (! function_exists('setEnvValue')) {
    function setEnvValue(string $key, string $value): void
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            return;
        }

        $escapedValue = '"'.trim(str_replace('"', '\"', $value)).'"';
        $envContent = file_get_contents($envPath);

        if (preg_match("/^{$key}=.*/m", $envContent)) {
            $envContent = preg_replace(
                "/^{$key}=.*/m",
                "{$key}={$escapedValue}",
                $envContent
            );
        } else {
            $envContent .= PHP_EOL."{$key}={$escapedValue}";
        }

        file_put_contents($envPath, $envContent);
    }
}

if (! function_exists('timezone_list')) {

    function timezone_list()
    {
        $zones_array = [];
        $timestamp = time();
        foreach (timezone_identifiers_list() as $key => $zone) {
            date_default_timezone_set($zone);
            $zones_array[$key]['ZONE'] = $zone;
            $zones_array[$key]['GMT'] = 'UTC/GMT '.date('P', $timestamp);
        }

        return $zones_array;
    }
}

if (! function_exists('create_timezone_option')) {

    function create_timezone_option($old = '')
    {
        $option = '';
        $timestamp = time();
        foreach (timezone_identifiers_list() as $key => $zone) {
            date_default_timezone_set($zone);
            $selected = $old == $zone ? 'selected' : '';
            $option .= '<option value="'.$zone.'"'.$selected.'>'.'GMT '.date('P', $timestamp).' '.$zone.'</option>';
        }
        echo $option;
    }
}

if (! function_exists('generate_select')) {
    function generate_select(
        $name,
        $options = [],
        $label = null,
        $selected = null,
        $required = false,
        $emptyLabel = '--Select--',
        $class = 'form-select form-select-sm form-select-solid',
        $id = null,
        $onchange = null,
        bool $enableRequiredMark = true,
        $isMultiple = false
    ) {
        $select = '';
        $requiredText = '';

        // If request is for backend
        $requiredText = ($required && $enableRequiredMark) ? "<span class='text-danger'>*</span>" : '';

        if (! empty($label)) {
            $select = "<label class='label-control my-2'>$label ".$requiredText.'</label>';
        }

        if ($isMultiple) {
            $name = $name.'[]';
        }

        $select .= "<select name='$name' "
            .(! empty($id) ? "id='$id' " : '')
            .(! empty($onchange) ? "onchange='$onchange' " : '')
            ."class='$class' "
            .($required ? 'required ' : '')
            .($isMultiple ? 'multiple ' : '')
            ."data-control='select2'>";

        if (! empty($emptyLabel)) {
            $select .= "<option value='' disabled>$emptyLabel</option>";
        }

        foreach ($options as $key => $value) {
            $select .= "<option value='$key' ".($selected == $key ? 'selected' : '').">$value</option>";
        }

        $select = $select.'</select>';

        return $select;
    }
}

if (! function_exists('get_subject_types')) {
    function get_subject_types()
    {
        return [
            'Compulsory' => _lang('Compulsory'),
            'Optional' => _lang('Optional'),
        ];
    }
}

if (! function_exists('get_section_name')) {
    function get_section_name($id)
    {
        $class = DB::table('sections')->where('id', $id)->get();
        if (! $class->isEmpty()) {
            return $class[0]->section_name;
        }

        return '';
    }
}

if (! function_exists('get_subject_name')) {
    function get_subject_name($id)
    {
        $class = DB::table('subjects')->where('id', $id)->get();
        if (! $class->isEmpty()) {
            return $class[0]->subject_name;
        }

        return '';
    }
}

if (! function_exists('get_subject_all_types')) {
    function get_subject_all_types()
    {
        return [
            'All' => _lang('All Groups'),
            'Compulsory' => _lang('Compulsory'),
            'Elective' => _lang('Elective'),
            'Optional' => _lang('Optional'),
            'Elective_Optional' => _lang('Elective & Optional Both'),
        ];
    }
}

if (! function_exists('get_blood_groups')) {
    function get_blood_groups(): array
    {
        return [
            'A+' => 'A+',
            'A-' => 'A-',
            'B+' => 'B+',
            'B-' => 'B-',
            'O+' => 'O+',
            'O-' => 'O-',
            'AB+' => 'AB+',
            'AB-' => 'AB-',
        ];
    }
}

if (! function_exists('get_blood_groups_select')) {
    function get_blood_groups_select($inputName, $label, $selected = '', $required = false, $class = 'form-control')
    {
        return generate_select($inputName, get_blood_groups(), $label, $selected, $required, _lang('--Select Group--'), $class);
    }
}

if (! function_exists('get_academic_year')) {
    function get_academic_year($id = '')
    {
        if ($id == '') {
            $id = get_option('academic_year');
        }
        $query = DB::table('academic_years')->where('id', $id)->get();
        if (! $query->isEmpty()) {
            return $query[0]->id;
        }

        return '';
    }
}

if (! function_exists('current_year')) {
    function current_year()
    {
        $currentYear = Carbon::now()->year;

        return $currentYear;
    }
}

if (! function_exists('get_class_name')) {
    function get_class_name($id)
    {
        $class = DB::table('classes')->where('id', $id)->get();
        if (! $class->isEmpty()) {
            return $class[0]->class_name;
        }

        return '';
    }
}

if (! function_exists('get_accounting_types')) {
    function get_accounting_types(?string $key = null): array|string
    {
        $types = [
            VoucherType::PAYMENT => _lang('Payment'),
            VoucherType::RECEIPT => _lang('Receipt'),
            VoucherType::CONTRA => _lang('Contra'),
            VoucherType::FUND_TRANSFER => _lang('Fund Transfer'),
            VoucherType::JOURNAL => _lang('Journal'),
        ];

        if ($key) {
            return $types[$key] ?? '';
        }

        return $types;
    }
}

if (! function_exists('get_old_value')) {
    function get_old_value($student, $key = '')
    {
        if (empty($student)) {
            return old($key);
        }

        return $student->$key;
    }
}

if (! function_exists('get_parent_id')) {
    function get_parent_id()
    {
        return DB::table('parent_models')
            ->where('user_id', Auth::id())
            ->value('id');
    }
}

if (! function_exists('get_parent_child_ids')) {
    /**
     * Ids of every student that genuinely belongs to the logged-in parent.
     * This is the single authorization boundary for the whole Parent panel:
     * any child id the panel acts on must be a member of this set.
     *
     * @return array<int>
     */
    function get_parent_child_ids(): array
    {
        static $cache = [];

        $user = Auth::user();
        if (! $user) {
            return [];
        }

        if (array_key_exists($user->id, $cache)) {
            return $cache[$user->id];
        }

        $parentId = DB::table('parent_models')->where('user_id', $user->id)->value('id');
        if (! $parentId) {
            return $cache[$user->id] = [];
        }

        return $cache[$user->id] = DB::table('students')
            ->where('parent_id', $parentId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}

if (! function_exists('parent_owns_child')) {
    /**
     * True only when $studentId is one of the logged-in parent's own children.
     */
    function parent_owns_child($studentId): bool
    {
        return in_array((int) $studentId, get_parent_child_ids(), true);
    }
}

if (! function_exists('get_authenticated_parent_student_id')) {
    /**
     * The currently "active" child for the logged-in parent.
     *
     * Resolution order:
     *   1. The child selected for the current web session — but ONLY if it is truly
     *      one of this parent's children. This ownership check is what prevents a
     *      parent from viewing another family's data by tampering with the session.
     *   2. The stored default child (parent_models.student_id) when still valid.
     *   3. The parent's first child.
     *
     * On stateless API requests there is no session, so resolution simply falls
     * through to the stored default — preserving the original API behaviour.
     */
    function get_authenticated_parent_student_id()
    {
        $childIds = get_parent_child_ids();
        if (empty($childIds)) {
            return null;
        }

        // 1. Session selection (web panel), gated by ownership.
        if (request()->hasSession()) {
            $sessionChild = (int) session('parent_active_child_id');
            if ($sessionChild && in_array($sessionChild, $childIds, true)) {
                return $sessionChild;
            }
        }

        // 2. Stored default child, if still a valid child.
        $defaultChild = (int) DB::table('parent_models')
            ->where('user_id', Auth::id())
            ->value('student_id');
        if ($defaultChild && in_array($defaultChild, $childIds, true)) {
            return $defaultChild;
        }

        // 3. First child as a safe fallback.
        return $childIds[0];
    }
}

if (! function_exists('get_parent_children')) {
    /**
     * The logged-in parent's children, each decorated with their current-session
     * class and section names. Used by the global child switcher in the layout,
     * so it is memoised per-request to avoid repeat queries on every page load.
     *
     * @return Collection
     */
    function get_parent_children()
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $user = Auth::user();
        if (! $user || $user->user_type !== 'Parent') {
            return $cache = collect();
        }

        $parentId = DB::table('parent_models')->where('user_id', $user->id)->value('id');
        if (! $parentId) {
            return $cache = collect();
        }

        $children = DB::table('students')
            ->leftJoin('users', 'users.id', '=', 'students.user_id')
            ->where('students.parent_id', $parentId)
            ->select('students.id', 'students.first_name', 'students.last_name', 'users.image')
            ->orderBy('students.first_name')
            ->get();

        if ($children->isEmpty()) {
            return $cache = $children;
        }

        // Attach class/section from the current academic year in a single extra query
        // (keyed lookup — no N+1 as the child count grows).
        $sessions = DB::table('student_sessions')
            ->leftJoin('classes', 'classes.id', '=', 'student_sessions.class_id')
            ->leftJoin('sections', 'sections.id', '=', 'student_sessions.section_id')
            ->whereIn('student_sessions.student_id', $children->pluck('id'))
            ->where('student_sessions.session_id', get_option('academic_year'))
            ->select('student_sessions.student_id', 'classes.class_name', 'sections.section_name')
            ->get()
            ->keyBy('student_id');

        $children->each(function ($child) use ($sessions) {
            $child->class_name = $sessions[$child->id]->class_name ?? null;
            $child->section_name = $sessions[$child->id]->section_name ?? null;
        });

        return $cache = $children;
    }
}

if (! function_exists('get_student_id')) {
    function get_student_id()
    {
        return DB::table('students')
            ->where('user_id', Auth::id())
            ->value('id');
    }
}

if (! function_exists('get_student_name')) {
    function get_student_name($student_id)
    {
        $student = DB::select("SELECT first_name,last_name FROM students
		WHERE id='$student_id'");

        return $student[0]->first_name;
    }
}

if (! function_exists('get_teacher_id')) {
    function get_teacher_id()
    {
        return DB::table('teachers')
            ->where('user_id', Auth::id())
            ->value('id');
    }
}

if (! function_exists('format_currency')) {
    /**
     * The currencies table is the source of truth for the symbol; settings
     * only record which currency is selected (currency_id) and where the
     * symbol goes (currency_position). Institutes that existed before the
     * currency catalog and never selected one fall back to their legacy
     * currency_symbol string, then to '$'.
     */
    function format_currency($amount)
    {
        static $symbolById = [];

        $currencyId = get_option('currency_id');
        $position = get_option('currency_position') ?: 'left';
        $symbol = null;

        if (! empty($currencyId)) {
            if (! array_key_exists($currencyId, $symbolById)) {
                $symbolById[$currencyId] = DB::table('currencies')
                    ->where('id', $currencyId)
                    ->where('status', 1)
                    ->whereNull('deleted_at')
                    ->value('symbol');
            }
            $symbol = $symbolById[$currencyId];
        }

        if ($symbol === null || $symbol === '') {
            $symbol = get_option('currency_symbol');
        }

        if ($symbol === '' || $symbol === null) {
            $symbol = '$';
        }

        $formatted = number_format((float) $amount, 2);

        return $position === 'right' ? $formatted.$symbol : $symbol.$formatted;
    }
}

if (! function_exists('convert_amount_to_words')) {
    function convert_amount_to_words($number)
    {
        $currencyName = active_currency_name();

        // If Intl extension is available
        if (class_exists('NumberFormatter')) {
            $f = new NumberFormatter('en', NumberFormatter::SPELLOUT);

            return ucfirst($f->format($number)).' '.$currencyName.' Only';
        }

        // Fallback (without Intl)
        return ucfirst(numberToWordsFallback($number)).' '.$currencyName.' Only';
    }
}

if (! function_exists('active_currency_name')) {
    /**
     * The selected currency's display name (e.g. "US Dollar"), for spelled-out
     * amounts (convert_amount_to_words). Falls back to the legacy hardcoded
     * "Taka" for institutes that existed before the currency catalog and
     * never selected one — matches this function's original behaviour exactly.
     */
    function active_currency_name(): string
    {
        static $nameById = [];

        $currencyId = get_option('currency_id');
        if (empty($currencyId)) {
            return 'Taka';
        }

        if (! array_key_exists($currencyId, $nameById)) {
            $nameById[$currencyId] = DB::table('currencies')
                ->where('id', $currencyId)
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->value('name');
        }

        return $nameById[$currencyId] ?: 'Taka';
    }
}

if (! function_exists('numberToWordsFallback')) {
    function numberToWordsFallback($number)
    {
        $hyphen = '-';
        $conjunction = ' and ';
        $separator = ', ';
        $negative = 'negative ';
        $decimal = ' point ';

        $dictionary = [
            0 => 'zero',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen',
            17 => 'seventeen',
            18 => 'eighteen',
            19 => 'nineteen',
            20 => 'twenty',
            30 => 'thirty',
            40 => 'forty',
            50 => 'fifty',
            60 => 'sixty',
            70 => 'seventy',
            80 => 'eighty',
            90 => 'ninety',
            100 => 'hundred',
            1000 => 'thousand',
            100000 => 'lakh',
            10000000 => 'crore',
        ];

        if (! is_numeric($number)) {
            return false;
        }

        if ($number < 21) {
            return $dictionary[$number];
        }

        if ($number < 100) {
            $tens = ((int) ($number / 10)) * 10;
            $units = $number % 10;

            return $dictionary[$tens].($units ? $hyphen.$dictionary[$units] : '');
        }

        if ($number < 1000) {
            $hundreds = (int) ($number / 100);
            $remainder = $number % 100;

            return $dictionary[$hundreds].' '.$dictionary[100].
                ($remainder ? $conjunction.numberToWordsFallback($remainder) : '');
        }

        if ($number < 100000) {
            $thousands = (int) ($number / 1000);
            $remainder = $number % 1000;

            return numberToWordsFallback($thousands).' thousand'.
                ($remainder ? $separator.numberToWordsFallback($remainder) : '');
        }

        if ($number < 10000000) {
            $lakhs = (int) ($number / 100000);
            $remainder = $number % 100000;

            return numberToWordsFallback($lakhs).' lakh'.
                ($remainder ? $separator.numberToWordsFallback($remainder) : '');
        }

        // Crore
        $crores = (int) ($number / 10000000);
        $remainder = $number % 10000000;

        return numberToWordsFallback($crores).' crore'.
            ($remainder ? $separator.numberToWordsFallback($remainder) : '');
    }
}

if (! function_exists('object_to_string')) {
    function object_to_string($object, $col, $quote = false)
    {
        $string = '';
        foreach ($object as $data) {
            if ($quote == true) {
                $string .= "'".$data->$col."', ";
            } else {
                $string .= $data->$col.', ';
            }
        }
        $string = substr_replace($string, '', -2);

        return $string;
    }
}

if (! function_exists('printSignature')) {
    function printSignature($id = null)
    {
        // A hardcoded default of Signature ID 1 leaked whichever institute happened to own
        // that row to every other tenant. Scope to the acting institute/branch, and only
        // fall back to an explicit $id when the caller actually passed one.
        $query = Signature::query();

        if ($id) {
            $query->where('id', $id);
        } else {
            $query->where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id());
        }

        $signature = $query->latest()->first();

        if ($signature && $signature->image) {
            // fileUploader() (used by SignatureService) writes to the public disk under
            // signatures/, which resolves to storage/signatures/ — not uploads/.
            $imagePath = asset('storage/signatures/'.$signature->image);

            return "<img class=\"printSignature\" src=\"{$imagePath}\" height=\"70px\" width=\"250px\" alt=\"\">";
        }

        return '';
    }
}

if (! function_exists('user_count')) {
    function user_count($user_type)
    {
        $count = User::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('user_type', $user_type)
            ->selectRaw('COUNT(id) as total')
            ->first()->total;

        return $count;
    }
}

if (! function_exists('get_notices')) {
    function get_notices($user_type = 'Website', $limit = 5)
    {
        $notices = Notice::join('user_notices', 'notices.id', 'user_notices.notice_id')
            ->select('notices.*')
            ->where('user_notices.user_type', $user_type)
            ->where('notices.institute_id', get_institute_id())
            ->where('notices.branch_id', get_branch_id())
            ->orderBy('notices.id', 'desc')
            ->limit($limit)
            ->get();

        return $notices;
    }
}

if (! function_exists('get_events')) {
    function get_events($limit = 5)
    {
        $events = Event::limit($limit)
            ->orderBy('id', 'desc')->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->get();

        return $events;
    }
}

if (! function_exists('fileUploader')) {
    function fileUploader(string $dir, string $format, $image = null, $oldImage = null)
    {
        if ($image == null) {
            return $oldImage ?? 'def.png';
        }

        // Delete old image(s) if exist
        if (! empty($oldImage)) {
            if (is_array($oldImage)) {
                foreach ($oldImage as $file) {
                    Storage::disk('public')->delete($dir.$file);
                }
            } elseif (is_string($oldImage)) {
                Storage::disk('public')->delete($dir.$oldImage);
            }
        }

        // Generate unique file name
        $imageName = Carbon::now()->toDateString().'-'.uniqid().'.'.$format;

        // Ensure directory exists
        if (! Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }

        // Store the uploaded file
        if ($image instanceof UploadedFile) {
            $image->storeAs($dir, $imageName, 'public');
        } else {
            throw new Exception('Invalid file type provided for upload.');
        }

        return $imageName;
    }
}

if (! function_exists('safeDocumentUploader')) {
    /**
     * For the handful of controllers (Syllabus, Assignment) that store uploads
     * directly under public/uploads/files/... via $file->move() instead of the
     * Storage disk fileUploader() uses. Those call sites named the saved file
     * with $file->getClientOriginalExtension() — the extension from the
     * client-supplied filename, not the file's actual content — so a real
     * zip/doc/pdf could still be saved with an attacker-chosen extension
     * (e.g. .pht) if the original filename carried one. This uses the
     * content-guessed extension instead, checks it against an explicit
     * allow-list even after Laravel's own mimes: validation, and generates a
     * collision-safe filename instead of a bare time().
     */
    function safeDocumentUploader(string $absoluteDir, UploadedFile $file, array $allowedExtensions, ?string $oldFileName = null): string
    {
        $extension = strtolower($file->extension() ?: $file->getClientOriginalExtension());

        if (! in_array($extension, $allowedExtensions, true)) {
            throw new Exception('Invalid file type provided for upload.');
        }

        if (! is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0755, true);
        }

        $fileName = Carbon::now()->toDateString().'-'.uniqid().'.'.$extension;
        $file->move($absoluteDir, $fileName);

        if ($oldFileName && file_exists($absoluteDir.$oldFileName)) {
            @unlink($absoluteDir.$oldFileName);
        }

        return $fileName;
    }
}

if (! function_exists('secure_upload')) {
    /**
     * Safely persist a user-uploaded document/attachment.
     *
     * Unlike the ad-hoc `$file->move(base_path('public/uploads/...'), time().rand().'.'.$file->getClientOriginalExtension())`
     * pattern scattered through the portals, this:
     *   - rejects anything whose real (server-sniffed) extension is not in the
     *     allow-list, and hard-blocks executable types regardless of the list;
     *   - never trusts the client filename — the stored name is random;
     *   - writes to the `public` disk (storage/app/public) via storeAs, not into
     *     the PHP-executable web root.
     *
     * @param  UploadedFile  $file
     * @return string the stored file name (persist this; prefix with $dir to read back)
     *
     * @throws ValidationException
     */
    function secure_upload($file, string $dir, array $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'zip']): string
    {
        $blocked = ['php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phar', 'pht', 'exe', 'sh', 'bat', 'cmd', 'com', 'cgi', 'pl', 'py', 'rb', 'js', 'jsp', 'asp', 'aspx', 'htaccess', 'svg', 'html', 'htm'];

        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            throw ValidationException::withMessages(['file' => _lang('The uploaded file is invalid.')]);
        }

        $ext = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());

        if (in_array($ext, $blocked, true) || ! in_array($ext, array_map('strtolower', $allowed), true)) {
            throw ValidationException::withMessages([
                'file' => _lang('This file type is not allowed.'),
            ]);
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'file' => _lang('The file may not be larger than 10 MB.'),
            ]);
        }

        $dir = trim($dir, '/').'/';
        if (! Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }

        $name = Str::uuid()->toString().'.'.$ext;
        $file->storeAs($dir, $name, 'public');

        return $name;
    }
}

if (! function_exists('secure_public_move')) {
    /**
     * Drop-in safe replacement for the legacy
     *   $file->move(base_path('public/'.$dir), time().rand().'.'.$file->getClientOriginalExtension())
     * pattern. Same destination and return shape (the file name), so existing
     * read paths keep working — but the extension is validated against an
     * allow-list / executable deny-list and the stored name is random, never
     * the client-supplied one.
     *
     * @param  UploadedFile  $file
     * @param  string  $dir  path relative to public/, e.g. 'uploads/files/assignments/'
     * @return string stored file name
     *
     * @throws ValidationException
     */
    function secure_public_move($file, string $dir, array $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'zip', 'mp3', 'mp4']): string
    {
        $blocked = ['php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phar', 'pht', 'exe', 'sh', 'bat', 'cmd', 'com', 'cgi', 'pl', 'py', 'rb', 'js', 'jsp', 'asp', 'aspx', 'htaccess', 'svg', 'html', 'htm'];

        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            throw ValidationException::withMessages(['file' => _lang('The uploaded file is invalid.')]);
        }

        $ext = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
        if (in_array($ext, $blocked, true) || ! in_array($ext, array_map('strtolower', $allowed), true)) {
            throw ValidationException::withMessages(['file' => _lang('This file type is not allowed.')]);
        }
        if ($file->getSize() > 10 * 1024 * 1024) {
            throw ValidationException::withMessages(['file' => _lang('The file may not be larger than 10 MB.')]);
        }

        $dir = trim($dir, '/').'/';
        $target = base_path('public/'.$dir);
        if (! is_dir($target)) {
            @mkdir($target, 0755, true);
        }

        $name = Str::uuid()->toString().'.'.$ext;
        $file->move($target, $name);

        return $name;
    }
}

if (! function_exists('fileRemover')) {
    function fileRemover(string $dir, $image)
    {
        if (! isset($image)) {
            return true;
        }

        if (Storage::disk('public')->exists($dir.$image)) {
            Storage::disk('public')->delete($dir.$image);
        }

        return true;
    }
}

if (! function_exists('sanitize_rich_text')) {
    /**
     * Sanitize HTML produced by a WYSIWYG editor (e.g. Summernote) before it is
     * saved, so it can still be rendered unescaped ({!! !!}) later without being
     * a stored-XSS vector. Keeps common formatting tags but strips anything that
     * can execute script: <script>/<iframe>/<object>/<embed>/<style>/<form>/<svg>
     * tags entirely, every on*="" event-handler attribute, and javascript:/
     * vbscript:/data: URIs in href/src.
     *
     * This is a pragmatic regex-based mitigation, not a full HTML parser — for
     * a hardened, industry-standard solution consider adding mews/purifier.
     */
    function sanitize_rich_text(?string $html): ?string
    {
        if ($html === null || $html === '') {
            return $html;
        }

        // Drop dangerous elements (and their content) entirely.
        $html = preg_replace('#<(script|iframe|object|embed|style|link|meta|form|input|svg|math)\b[^>]*>.*?</\1>#is', '', $html);
        $html = preg_replace('#<(script|iframe|object|embed|style|link|meta|form|input|svg|math)\b[^>]*/?>#is', '', $html);

        // Strip on*="..." / on*='...' event-handler attributes.
        $html = preg_replace('#\s+on[a-z]+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)#is', '', $html);

        // Neutralize javascript:/vbscript:/data: URIs in href/src attributes.
        $html = preg_replace('#(href|src)\s*=\s*(["\'])\s*(javascript|vbscript|data):[^"\']*\2#is', '$1=$2#$2', $html);

        return $html;
    }
}

if (! function_exists('sent_sms')) {
    function sent_sms($phone, $message)
    {
        try {
            $smsService = new SmsService;

            return $smsService->sendSMS($phone, $message);
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

if (! function_exists('configSettings')) {
    function configSettings($key, $settingsType)
    {
        try {
            $config = DB::table('settings')->where('key_name', $key)
                ->where('settings_type', $settingsType)->first();
        } catch (Exception $exception) {
            return null;
        }

        return (isset($config)) ? $config : null;
    }
}

if (! function_exists('businessConfig')) {
    function businessConfig($key, $settingsType = null)
    {
        try {
            $config = Setting::query()
                ->where('key_name', $key)
                ->when($settingsType, function ($query) use ($settingsType) {
                    $query->where('settings_type', $settingsType);
                })
                ->first();
        } catch (Exception $exception) {
            return null;
        }

        return (isset($config)) ? $config : null;
    }
}

if (! function_exists('getAuthUser')) {
    function getAuthUser(): ?object
    {
        return auth()->user();
    }
}

if (! function_exists('getUserId')) {
    function getUserId(): ?int
    {
        return auth()->user()?->id;
    }
}

if (! function_exists('getUserInstituteId')) {
    function getUserInstituteId()
    {
        $instituteId = Auth::user()->institute_id;

        return (int) $instituteId;
    }
}

if (! function_exists('getUserBranchId')) {
    function getUserBranchId()
    {
        $branchId = Auth::user()->branch_id;

        return (int) $branchId;
    }
}

if (! function_exists('getUser')) {
    function getUser(): ?object
    {
        return auth()->user();
    }
}

if (! function_exists('getStudent')) {
    function getStudent(): ?object
    {
        return auth()->user()?->student;
    }
}

if (! function_exists('getStudentId')) {
    function getStudentId(): ?int
    {
        return auth()->user()?->student?->id;
    }
}

function clearInstituteHomepageCache(int $instituteId)
{
    Cache::forget(
        'homepage_data_'.$instituteId
    );
}

if (! function_exists('whatsappSetting')) {
    function whatsappSetting()
    {
        return WhatsAppSetting::where('institute_id', get_institute_id())
            ->where(function ($query) {
                $query->where('branch_id', get_branch_id())->orWhereNull('branch_id');
            })
            ->where('status', 1)
            ->orderByRaw('branch_id IS NULL')
            ->first();
    }
}
