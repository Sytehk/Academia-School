<?php

namespace App\Services;

use App\Models\SystemModule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Nwidart\Modules\Facades\Module;

class ModuleManagerService
{
    public function syncFromNwidart(): void
    {
        if (! Schema::hasTable('system_modules')) {
            return;
        }

        foreach (Module::all() as $module) {
            $slug = $module->getLowerName();
            $json = $module->get('requires') ?? [];

            SystemModule::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $module->getName(),
                    'version' => $module->get('version', '1.0.0'),
                    'description' => $module->getDescription(),
                    'enabled' => $module->isEnabled(),
                    'status' => $module->isEnabled() ? 'active' : 'disabled',
                    'dependencies' => is_array($json) ? array_values($json) : [],
                    'minimum_app_version' => $module->get('minimum_app_version'),
                ]
            );
        }
    }

    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        $this->syncFromNwidart();

        return SystemModule::query()->orderBy('name')->get();
    }

    public function isEnabled(string $slug): bool
    {
        if (! Schema::hasTable('system_modules')) {
            return Module::findByName($slug)?->isEnabled() ?? true;
        }

        $record = SystemModule::query()->where('slug', $slug)->first();

        return $record ? $record->enabled : (Module::findByName($slug)?->isEnabled() ?? true);
    }

    public function enable(string $slug): void
    {
        $module = $this->findModuleRecord($slug);
        $this->ensureDependenciesMet($module);

        Module::find($module->name)?->enable();
        $module->update(['enabled' => true, 'status' => 'active']);
    }

    public function disable(string $slug, bool $skipDemoCheck = false): void
    {
        if (! $skipDemoCheck) {
            app(DemoModeService::class)->check('module_disable');
        }

        $module = $this->findModuleRecord($slug);

        if (in_array($module->name, config('system.modules.core_modules', []), true)) {
            throw new \RuntimeException("Core module [{$module->name}] cannot be disabled.");
        }

        $dependents = $this->getDependents($module->name);

        if ($dependents->isNotEmpty()) {
            throw new \RuntimeException(
                'This module cannot be disabled because the following modules depend on it: '
                .$dependents->pluck('name')->implode(', ')
            );
        }

        Module::find($module->name)?->disable();
        $module->update(['enabled' => false, 'status' => 'disabled']);
    }

    public function getDependents(string $moduleName): Collection
    {
        return SystemModule::query()
            ->where('enabled', true)
            ->get()
            ->filter(function (SystemModule $module) use ($moduleName) {
                $deps = $module->dependencies ?? [];

                return in_array($moduleName, $deps, true)
                    || in_array(strtolower($moduleName), array_map('strtolower', $deps), true);
            });
    }

    private function ensureDependenciesMet(SystemModule $module): void
    {
        foreach ($module->dependencies ?? [] as $dependency) {
            $depSlug = strtolower($dependency);
            $dep = SystemModule::query()->where('slug', $depSlug)->first()
                ?? SystemModule::query()->where('name', $dependency)->first();

            if ($dep && ! $dep->enabled) {
                throw new \RuntimeException("Required module [{$dependency}] must be enabled first.");
            }
        }
    }

    private function findModuleRecord(string $slug): SystemModule
    {
        $this->syncFromNwidart();

        $record = SystemModule::query()
            ->where('slug', strtolower($slug))
            ->orWhere('name', $slug)
            ->first();

        if (! $record) {
            throw new \RuntimeException("Module [{$slug}] not found.");
        }

        return $record;
    }
}
