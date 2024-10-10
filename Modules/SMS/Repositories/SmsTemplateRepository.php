<?php

declare(strict_types=1);

namespace Modules\SMS\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\SMS\Models\SmsTemplate;

class SmsTemplateRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return SmsTemplate::orderBy('id', 'desc')->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->latest()->get();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = SmsTemplate::query();

        return $query->orderBy('id', 'desc')->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create($data): ?SmsTemplate
    {
        $template = SmsTemplate::create($data);

        return $template;
    }

    public function update(array $data, $id): ?bool
    {
        $smsTemplate = SmsTemplate::find($id);
        if ($smsTemplate) {
            $smsTemplate->update($data);

            return true;
        }

        return false;
    }

    public function show(int $id): ?SmsTemplate
    {
        return SmsTemplate::find($id);
    }

    public function delete($id): ?bool
    {
        SmsTemplate::destroy($id);

        return true;
    }
}
