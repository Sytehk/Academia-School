<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleEntity extends Command
{
    protected $signature = 'make:module-entity {module} {name}';

    protected $description = 'Create module-based model, controller, repository, request, migration, and route';

    public function handle()
    {
        $module = Str::studly($this->argument('module'));
        $name = Str::studly($this->argument('name'));
        $pluralName = Str::pluralStudly($name);
        $snakePlural = Str::snake($pluralName);

        $basePath = base_path("Modules/{$module}");
        $paths = [
            'model' => "{$basePath}/app/Models/{$name}.php",
            'controller' => "{$basePath}/app/Http/Controllers/API/{$name}Controller.php",
            'storeRequest' => "{$basePath}/app/Http/Requests/{$name}/{$name}StoreRequest.php",
            'updateRequest' => "{$basePath}/app/Http/Requests/{$name}/{$name}UpdateRequest.php",
            'repository' => "{$basePath}/Repositories/{$name}Repository.php",
            'migration' => "{$basePath}/Database/Migrations/".date('Y_m_d_His')."_create_{$snakePlural}_table.php",
            'route' => "{$basePath}/routes/api.php",
        ];

        // Make directories
        foreach ($paths as $path) {
            File::ensureDirectoryExists(dirname($path));
        }

        // Generate Model with Fillable Template
        File::put($paths['model'], "<?php

namespace Modules\\{$module}\\Models;

use Illuminate\Database\Eloquent\Model;

class {$name} extends Model
{
    protected \$fillable = [
        'institute_id',
        'name',
        'status',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public const TABLE_NAME = '".$snakePlural."';

    protected \$table = self::TABLE_NAME;
}");

        // Controller with extended functionality
        File::put($paths['controller'], "<?php

namespace Modules\\{$module}\\Http\\Controllers\\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\\{$module}\\Http\\Requests\\{$name}\\{$name}StoreRequest;
use Modules\\{$module}\\Http\\Requests\\{$name}\\{$name}UpdateRequest;
use Modules\\{$module}\\Repositories\\{$name}Repository;

class {$name}Controller extends Controller
{
    public function __construct(private {$name}Repository \$repo) {}

    public function index(): JsonResponse
    {
        try {
            return \$this->responseSuccess(\$this->repo->getAll(request()->all()),'{$name} has been fetched successfully.');
        } catch (Exception \$e) {
            return \$this->responseError([], \$e->getMessage());
        }
    }

    public function store({$name}StoreRequest \$request): JsonResponse
    {
        try {
            return \$this->responseSuccess(\$this->repo->create(\$request->all()),'{$name} has been created successfully.');
        } catch (\Illuminate\Database\QueryException \$exception) {
            return \$this->responseError([], 'Database error: '.\$exception->getMessage());
        } catch (Exception \$e) {
            return \$this->responseError([], \$e->getMessage());
        }
    }

    public function show(\$id): JsonResponse
    {
         try {
            return \$this->responseSuccess(\$this->repo->getById(\$id), '{$name} has been fetched successfully.');
        } catch (Exception \$e) {
            return \$this->responseError([], \$e->getMessage());
        }
    }

    public function update({$name}UpdateRequest \$request, \$id): JsonResponse
    {
        try {
            return \$this->responseSuccess(\$this->repo->update(\$id, \$request->all()),'{$name} has been updated successfully.');
        } catch (Exception \$e) {
            return \$this->responseError([], \$e->getMessage());
        }
    }

    public function destroy(\$id): JsonResponse
    {
        try {
            return \$this->responseSuccess(\$this->repo->delete(\$id),'{$name} has been deleted successfully.');
        } catch (Exception \$e) {
            return \$this->responseError([], \$e->getMessage());
        }
    }
}");

        // Generate Migration Content
        File::put($paths['migration'], "<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$snakePlural}', function (Blueprint \$table) {
            \$table->id();
            \$table->unsignedBigInteger('institute_id')->nullable();
            \$table->string('name');
            \$table->smallInteger('status')->default(1)->comment('1=Active, 2=InActive');
            \$table->timestamps();
            \$table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$snakePlural}');
    }
};
");

        // Request classes
        File::put($paths['storeRequest'], "<?php

namespace Modules\\{$module}\\Http\\Requests\\{$name};

use Illuminate\Foundation\Http\FormRequest;

class {$name}StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // validation rules
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}");

        File::put($paths['updateRequest'], "<?php

namespace Modules\\{$module}\\Http\\Requests\\{$name};

use Illuminate\Foundation\Http\FormRequest;

class {$name}UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // validation rules
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}");

        // Repository with custom logic
        File::put($paths['repository'], "<?php

namespace Modules\\{$module}\\Repositories;

use App\Abstracts\EntityRepository;
use Modules\\{$module}\\Models\\{$name};
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Symfony\Component\HttpFoundation\Response;

class {$name}Repository extends EntityRepository
{
    public string \$table = {$name}::TABLE_NAME;

    protected array \$fillableColumns = [
        'institute_id',
        'name',
        'status',
        'created_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected function getQuery(): Builder
    {
        return parent::getQuery();
    }

    protected function getFilterData(array \$filterData = []): array
    {
        \$defaultArgs = [
            'perPage' => 10,
            'search' => '',
            'orderBy' => 'id',
            'order' => 'desc',
            'with_deleted' => false,
        ];

        return array_merge(\$defaultArgs, \$filterData);
    }

    private function get{$name}Query(): Builder
    {
        return \$this->getQuery()
            ->select(
                \"{\$this->table}.id\",
                \"{\$this->table}.institute_id\",
                \"{\$this->table}.name\",
                \"{\$this->table}.status\",
                \"{\$this->table}.created_at\",
                \"{\$this->table}.deleted_at\"
        );
    }

    protected function filterSearchQuery(Builder|EloquentBuilder \$query, string \$searchedText): Builder
    {
        \$searchable = \"%\$searchedText%\";

        return \$query->where(\"{\$this->table}.name\", 'LIKE', \$searchable)
                     ->orWhere(\"{\$this->table}.status\", 'LIKE', \$searchable);
    }

    public function getAll(array \$filterData = []): Paginator
    {
        \$filter = \$this->getFilterData(\$filterData);
        \$query = \$this->get{$name}Query();

        if (! \$filter['with_deleted']) {
            \$query->whereNull(\"{\$this->table}.deleted_at\");
        }

        if (!empty(\$filter['search'])) {
            \$query = \$this->filterSearchQuery(\$query, \$filter['search']);
        }

        return \$query
            ->orderBy(\$filter['orderBy'], \$filter['order'])
            ->paginate(\$filter['perPage']);
    }

    public function getCount(array \$filterData = []): int
    {
        \$filter = \$this->getFilterData(\$filterData);
        \$query = \$this->getQuery();

        if (! \$filter['with_deleted']) {
            \$query->whereNull(\"{\$this->table}.deleted_at\");
        }

        return \$query->count();
    }

    /**
     * @throws Exception
     */
    public function getByColumn(string \$columnName, \$columnValue, array \$selects = ['*']): ?object
    {
        \$item = \$this->get{$name}Query()
            ->where(\$columnName, \$columnValue)
            ->first(\$selects);

        if (empty(\$item)) {
            throw new Exception(
                \$this->getExceptionMessage(static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE),
                Response::HTTP_NOT_FOUND
            );
        }

        return \$item;
    }

    /**
     * @throws Exception
     */
    public function create(array \$data): object
    {
        \$data = \$this->prepareForDB(\$data);
        \$id = \$this->getQuery()->insertGetId(\$data);
        return {$name}::find(\$id);
    }

    /**
     * @throws Exception
     */
    public function update(int \$id, array \$data): object
    {
        \$item = {$name}::findOrFail(\$id);
        \$data = \$this->prepareForDB(\$data, \$item);
        parent::update(\$id, \$data);
        return \$this->getById(\$id);
    }

    /**
     * @throws Exception
     */
    public function prepareForDB(array \$data, ?object \$item = null): array
    {
        \$data = parent::prepareForDB(\$data, \$item);

        if (empty(\$item)) {
            \$data['created_at'] = now();
            \$data['institute_id'] = \$this->getCurrentInstituteId();
            \$data['status'] = 1;

            if (!empty(\$data['image']) && \$data['image'] instanceof \Illuminate\Http\UploadedFile) {
                \$data['image'] = fileUploader('{$module}/', 'png', \$data['image']);
            }
        } else {
            if (!empty(\$data['image']) && \$data['image'] instanceof \Illuminate\Http\UploadedFile) {
                \$data['image'] = fileUploader('{$module}/', 'png', \$data['image'], \$item->image);
            }

            \$data['updated_at'] = now();
        }

        return \$data;
    }

    protected function getExceptionMessages(): array
    {
        return [
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => '{$name} does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => '{$name} could not be deleted.',
        ];
    }
}
");

        // Create module route
        if (! File::exists($paths['route'])) {
            File::put($paths['route'], "<?php\n\nuse Illuminate\Support\Facades\Route;\n\n");
        }
        File::append($paths['route'], "\nRoute::apiResource('".Str::kebab($snakePlural)."', \\Modules\\{$module}\\Http\\Controllers\\API\\{$name}Controller::class);");

        // Add module route loader to RouteServiceProvider (if not already done manually)
        $this->info("✅ {$name} entity created under module {$module} with full structure!");
    }
}
