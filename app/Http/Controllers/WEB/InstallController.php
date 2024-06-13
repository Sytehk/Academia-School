<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Services\Installer\DatabaseInstaller;
use App\Services\Installer\EnvFileWriter;
use App\Services\Installer\InstallationState;
use App\Services\Installer\RequirementsChecker;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module;
use PDOException;
use Throwable;
use ZipArchive;

class InstallController extends Controller
{
    // 1️⃣ Show Start Page
    public function index()
    {
        InstallationState::markStepComplete('start');

        return view('install.start');
    }

    // 2️⃣ Requirements Check
    public function requirements()
    {
        if (! InstallationState::canAccess('requirements')) {
            return redirect()->route('install.start');
        }

        $requirements = RequirementsChecker::check();
        $next = RequirementsChecker::allPass($requirements);

        if ($next) {
            InstallationState::markStepComplete('requirements');
        }

        return view('install.requirements', compact('requirements', 'next'));
    }

    // 3️⃣ Permissions Check
    public function permissions()
    {
        if (! InstallationState::canAccess('permissions')) {
            return redirect()->route('install.requirements');
        }

        $permissions = RequirementsChecker::checkPermissions();
        $next = ! in_array(false, $permissions, true);

        if ($next) {
            InstallationState::markStepComplete('permissions');
        }

        return view('install.permissions', compact('permissions', 'next'));
    }

    // 4️⃣ Show Database Form
    public function showDatabaseForm()
    {
        if (! InstallationState::canAccess('database')) {
            return redirect()->route('install.permissions');
        }

        return view('install.database');
    }

    // 6️⃣ AJAX: Test DB Connection
    public function checkDatabase(Request $request)
    {
        if (! InstallationState::canAccess('database')) {
            return response()->json(['status' => 'error', 'message' => 'Please complete the earlier steps first.'], 403);
        }

        $validated = $this->validateDbCredentials($request);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        try {
            $pdo = DatabaseInstaller::connect(
                $validated['host'], (string) $validated['port'], $validated['name'],
                $validated['username'], $validated['password'] ?? ''
            );

            $privileges = DatabaseInstaller::checkPrivileges($pdo, $validated['name']);
            $missing = array_keys(array_filter($privileges, fn ($ok) => ! $ok));

            if (! empty($missing)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Database connection succeeded, but the user is missing required privileges: '.implode(', ', $missing).'.',
                    'privileges' => $privileges,
                ]);
            }

            $tableCount = DatabaseInstaller::tableCount($pdo, $validated['name']);

            return response()->json([
                'status' => 'success',
                'privileges' => $privileges,
                'existing_tables' => $tableCount,
                'warning' => $tableCount > 0 ? "This database already contains {$tableCount} table(s). Continuing will overwrite them." : null,
            ]);
        } catch (Exception $e) {
            Log::warning('Installer: database check failed', ['error' => $e->getMessage()]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // 7️⃣ Save DB Credentials & Proceed
    public function saveDatabase(Request $request)
    {
        if (! InstallationState::canAccess('database')) {
            return response()->json(['status' => 'error', 'message' => 'Please complete the earlier steps first.'], 403);
        }

        $validated = $this->validateDbCredentials($request, requireSoftwareName: true);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        try {
            $pdo = DatabaseInstaller::connect(
                $validated['host'], (string) $validated['port'], $validated['name'],
                $validated['username'], $validated['password'] ?? ''
            );
            $privileges = DatabaseInstaller::checkPrivileges($pdo, $validated['name']);
            if (in_array(false, $privileges, true)) {
                return response()->json(['status' => 'error', 'message' => 'The database user does not have sufficient privileges. Please fix this before continuing.']);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Database connection failed: '.$e->getMessage()]);
        }

        EnvFileWriter::set([
            'APP_NAME' => $validated['software_name'],
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $validated['host'],
            'DB_PORT' => $validated['port'],
            'DB_DATABASE' => $validated['name'],
            'DB_USERNAME' => $validated['username'],
            'DB_PASSWORD' => $validated['password'] ?? '',
        ]);

        $this->ensureAppKey();

        InstallationState::markStepComplete('database');

        return response()->json(['status' => 'success', 'redirect' => url('install/installation')]);
    }

    /**
     * @return array{host:string,port:string,name:string,username:string,password:string,software_name?:string}|JsonResponse
     */
    private function validateDbCredentials(Request $request, bool $requireSoftwareName = false)
    {
        $rules = [
            'host' => 'required|string|max:255',
            'port' => 'required|numeric|min:1|max:65535',
            'name' => 'required|string|max:64|regex:/^[A-Za-z0-9_\-]+$/',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string|max:255',
        ];

        if ($requireSoftwareName) {
            $rules['software_name'] = 'required|string|max:255';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        return $validator->validated();
    }

    /**
     * Ensure APP_KEY exists without ever regenerating an existing one
     * (which would invalidate already-encrypted session/data).
     */
    private function ensureAppKey(): void
    {
        if (! empty(env('APP_KEY'))) {
            return;
        }

        Artisan::call('key:generate', ['--force' => true]);
    }

    public function installation(Request $request)
    {
        if (! $request->isMethod('post')) {
            if (! InstallationState::canAccess('installation')) {
                return redirect()->route('install.database');
            }

            return view('install.installation');
        }

        if (InstallationState::isInstalled()) {
            return redirect('/');
        }

        if (! InstallationState::canAccess('installation')) {
            return redirect()->route('install.database');
        }

        $host = env('DB_HOST', '127.0.0.1');
        $port = (string) env('DB_PORT', '3306');
        $name = env('DB_DATABASE', '');
        $user = env('DB_USERNAME', '');
        $pass = env('DB_PASSWORD', '');

        try {
            // Re-verify connectivity + privileges right before touching data —
            // the .env may have been hand-edited since the earlier AJAX check,
            // which alone is not trusted.
            $pdo = DatabaseInstaller::connect($host, $port, $name, $user, $pass);
            $privileges = DatabaseInstaller::checkPrivileges($pdo, $name);
            if (in_array(false, $privileges, true)) {
                $missing = implode(', ', array_keys(array_filter($privileges, fn ($ok) => ! $ok)));

                throw new Exception("Installation cannot proceed: the database user is missing required privileges ({$missing}).");
            }

            $sqlPath = base_path('demo.sql');
            if (! file_exists($sqlPath)) {
                throw new Exception('demo.sql file not found in the application root folder.');
            }

            // Only now — after connectivity, privileges and the SQL file itself
            // are all confirmed present — do we touch existing data.
            Artisan::call('db:wipe', ['--force' => true]);

            DatabaseInstaller::importSqlFile($pdo, $sqlPath);

            $this->ensureAppKey();
            $this->linkStorage();
            $this->finalizeCaches();

            InstallationState::markInstalled();
            InstallationState::resetProgress();

            return view('install.complete');
        } catch (Throwable $e) {
            Log::error('Installation failed', [
                'step' => 'installation',
                'error' => $e->getMessage(),
            ]);

            $message = $e instanceof PDOException || $e instanceof Exception
                ? $this->safeErrorMessage($e)
                : 'An unexpected error occurred during installation. Please check storage/logs/laravel.log for details.';

            return redirect()->back()->withErrors(['error' => $message]);
        }
    }

    /**
     * Never leak raw PDO/SQL exception text (which can include the DB
     * password if the driver embeds the DSN in its error) to the browser.
     */
    private function safeErrorMessage(Throwable $e): string
    {
        $message = $e->getMessage();

        // These messages are already hand-written and safe (from DatabaseInstaller
        // or explicit throws in this controller); anything else is sanitized.
        $knownSafePrefixes = [
            'Installation cannot proceed', 'demo.sql file not found', 'SQL file not found',
            'Failed executing SQL statement', 'Access denied', 'The specified database',
            'Connection refused', 'Connection timed out', 'This database host',
            'Could not connect', 'Invalid character', 'Database port must be numeric',
        ];

        foreach ($knownSafePrefixes as $prefix) {
            if (str_starts_with($message, $prefix)) {
                return $message;
            }
        }

        return 'Installation failed due to a database error. See storage/logs/laravel.log for details.';
    }

    private function linkStorage(): void
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        if (is_link($link) || file_exists($link)) {
            return;
        }

        File::ensureDirectoryExists($target);

        try {
            Artisan::call('storage:link');

            return;
        } catch (Throwable) {
            // symlink() disabled (common on shared hosting) — fall back to a
            // directory copy so uploaded files are still served, and log the
            // fact that a real symlink is preferred once available.
        }

        if (! is_link($link) && ! file_exists($link)) {
            File::copyDirectory($target, $link);
            Log::warning('Installer: symlink() unavailable, copied storage/app/public to public/storage instead. Re-run "php artisan storage:link" manually once symlinks are supported on this host.');
        }
    }

    private function finalizeCaches(): void
    {
        // Never cache config before this point — .env has only just been finalized.
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('cache:clear');
    }

    // 9️⃣ Complete (health-check page, reachable only post-install)
    public function complete()
    {
        if (! InstallationState::isInstalled()) {
            return redirect()->route('install.start');
        }

        return view('install.complete');
    }

    // 10️⃣ Delete Migrations & Seeders — intentionally unused/unrouted; kept only
    // for reference. Left disabled: irreversible and unnecessary once installs
    // are driven by demo.sql rather than migrations.
    private function deleteInstallationFiles()
    {
        foreach ([database_path('migrations'), database_path('seeders')] as $path) {
            foreach (glob($path.'/*.php') as $file) {
                @unlink($file);
            }
        }

        foreach (Module::all() as $module) {
            foreach (glob(module_path($module->getName()).'/Database/Migrations/*.php') as $file) {
                @unlink($file);
            }
            foreach (glob(module_path($module->getName()).'/Database/Seeders/*.php') as $file) {
                @unlink($file);
            }
        }
    }

    // 12️⃣ Upgrade Logic
    public function upgradeIndex(Request $request)
    {
        $this->authorizeUpgrade();

        return view('upgrade');
    }

    public function uploadStore(Request $request)
    {
        $this->authorizeUpgrade();

        $request->validate(['upgrade_zip' => 'required|file|mimes:zip|max:512000']);
        $file = $request->file('upgrade_zip');
        $zip = new ZipArchive;
        $filePath = storage_path('app/temp_upgrade_'.Str::random(16).'.zip');
        $file->move(dirname($filePath), basename($filePath));

        if ($zip->open($filePath) !== true) {
            File::delete($filePath);

            return back()->with('error', 'Failed to open the upgrade archive.');
        }

        // Reject archives containing path traversal / absolute paths before ever touching disk (zip-slip guard).
        $basePath = base_path();
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            $normalized = str_replace('\\', '/', $entryName);

            if (str_contains($normalized, '../') || str_starts_with($normalized, '/') || preg_match('/^[A-Za-z]:/', $normalized)) {
                $zip->close();
                File::delete($filePath);

                return back()->with('error', 'Upgrade archive rejected: unsafe file path detected ('.$entryName.').');
            }

            $resolved = realpath($basePath).DIRECTORY_SEPARATOR.$normalized;
            if (! str_starts_with(str_replace('\\', '/', $resolved), str_replace('\\', '/', realpath($basePath)))) {
                $zip->close();
                File::delete($filePath);

                return back()->with('error', 'Upgrade archive rejected: unsafe file path detected.');
            }
        }

        $zip->extractTo($basePath);
        $zip->close();
        File::delete($filePath);

        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('view:clear');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');

        return back()->with('success', 'Upgrade applied successfully!');
    }

    /**
     * Only an authenticated SAAS Admin may run the upgrade flow.
     */
    private function authorizeUpgrade(): void
    {
        $user = auth()->user();

        abort_unless($user && $user->user_type === 'SAAS Admin', 403, 'Only a SAAS Admin can perform an upgrade.');
    }
}
