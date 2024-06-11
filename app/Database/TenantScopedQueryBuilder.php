<?php

namespace App\Database;

use App\Support\TenantTables;
use Exception;
use Illuminate\Database\Query\Builder;
use Symfony\Component\HttpFoundation\Response;

/**
 * Query builder handed out by EntityRepository::getQuery() for the tables in
 * TenantTables. Reads are scoped by the repository; this class guards the
 * writes that bypass Eloquent entirely:
 *
 *  - insert()/insertGetId() always stamp the caller's institute_id (a client
 *    can no longer post someone else's institute_id through `fillable`);
 *  - update()/delete() refuse to touch a shared (NULL-institute) row unless the
 *    caller is the SAAS Admin, and never touch another institute's row.
 */
class TenantScopedQueryBuilder extends Builder
{
    public function insert(array $values)
    {
        return parent::insert($this->stamp($values));
    }

    public function insertGetId(array $values, $sequence = null)
    {
        return parent::insertGetId($this->stampRow($values), $sequence);
    }

    public function update(array $values)
    {
        $this->guardWrite();
        unset($values['institute_id']);

        return parent::update($values);
    }

    public function delete($id = null)
    {
        if ($id !== null) {
            $this->where($this->from.'.id', '=', $id);
        }
        $this->guardWrite();

        return parent::delete();
    }

    private function guardWrite(): void
    {
        $table = (string) $this->from;

        $blocked = (clone $this)->where(function ($query) use ($table) {
            $query->whereNull($table.'.institute_id')
                ->orWhere($table.'.institute_id', '!=', (int) get_institute_id());
        });

        if (! TenantTables::isSaasAdmin() && $blocked->exists()) {
            throw new Exception(
                _lang('Shared question bank entries are read-only. Create your own entry instead.'),
                Response::HTTP_FORBIDDEN
            );
        }

        TenantTables::applyWriteScope($this, $table);
    }

    private function stamp(array $values): array
    {
        if ($values === [] || ! is_array(reset($values))) {
            return $this->stampRow($values);
        }

        return array_map(fn (array $row) => $this->stampRow($row), $values);
    }

    private function stampRow(array $row): array
    {
        $instituteId = get_institute_id();

        // A SAAS Admin without an institute seeds the shared library (NULL).
        if (TenantTables::isSaasAdmin() && ! $instituteId && TenantTables::isShared((string) $this->from)) {
            $row['institute_id'] = null;

            return $row;
        }

        $row['institute_id'] = $instituteId;

        return $row;
    }
}
