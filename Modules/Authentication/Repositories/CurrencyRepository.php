<?php

namespace Modules\Authentication\Repositories;

use App\Abstracts\EntityRepository;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Modules\Authentication\Models\Currency;
use Symfony\Component\HttpFoundation\Response;

class CurrencyRepository extends EntityRepository
{
    public string $table = Currency::TABLE_NAME;

    protected array $fillableColumns = [
        'name',
        'symbol',
        'code',
        'country_code',
        'status',
    ];

    protected function getFilterData(array $filterData = []): array
    {
        $defaultArgs = [
            'perPage' => 10,
            'search' => '',
            'orderBy' => 'id',
            'order' => 'desc',
            'with_deleted' => false,
        ];

        return array_merge($defaultArgs, $filterData);
    }

    protected function filterSearchQuery(Builder|EloquentBuilder $query, string $searchedText): Builder
    {
        $searchable = "%$searchedText%";

        return $query->where("{$this->table}.name", 'LIKE', $searchable)
            ->orWhere("{$this->table}.code", 'LIKE', $searchable);
    }

    public function getAll(array $filterData = []): Paginator
    {
        $filter = $this->getFilterData($filterData);
        $query = $this->getQuery();

        if (! $filter['with_deleted']) {
            $query->whereNull("{$this->table}.deleted_at");
        }

        if (! empty($filter['search'])) {
            $query = $this->filterSearchQuery($query, $filter['search']);
        }

        return $query
            ->orderBy($filter['orderBy'], $filter['order'])
            ->paginate($filter['perPage']);
    }

    /**
     * @throws Exception
     */
    public function create(array $data): object
    {
        $data = $this->prepareForDB($data);
        $data['created_at'] = now();
        $data['updated_at'] = now();
        $id = $this->getQuery()->insertGetId($data);

        return Currency::findOrFail($id);
    }

    /**
     * @throws Exception
     */
    public function update(int $id, array $data): object
    {
        Currency::findOrFail($id);
        $data = $this->prepareForDB($data);
        $data['updated_at'] = now();
        parent::update($id, $data);

        return Currency::findOrFail($id);
    }

    /**
     * A currency in use by any institute's `currency_id` setting cannot be
     * deleted — doing so would leave format_currency() unable to resolve a
     * symbol for that institute. Deactivating it (status update) is the
     * correct way to retire a currency that institutes still reference.
     *
     * @throws Exception
     */
    public function delete(int $id): ?object
    {
        $item = Currency::findOrFail($id);

        $inUse = DB::table('settings')
            ->where('name', 'currency_id')
            ->where('value', (string) $id)
            ->exists();

        if ($inUse) {
            throw new Exception(
                'This currency is selected by at least one institute and cannot be deleted. Deactivate it instead.',
                Response::HTTP_CONFLICT
            );
        }

        return parent::delete($id);
    }

    protected function getExceptionMessages(): array
    {
        return [
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'Currency does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'Currency could not be deleted.',
        ];
    }
}
