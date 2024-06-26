<?php

declare(strict_types=1);

namespace Modules\Academic\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Academic\Models\Contact;

class ContactUsRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return Contact::orderBy('id', 'desc')->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->get();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        return Contact::orderBy('id', 'desc')->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create(array $data): Contact
    {
        return Contact::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return Contact::find($id)->update($data);
    }

    public function delete(int $id): int
    {
        return Contact::destroy($id);
    }

    public function show(int $id): ?Contact
    {
        return Contact::find($id);
    }

    public function changeMessageStatus(int $id): bool
    {
        $message = $this->show((int) $id);

        if (! $message) {
            abort(404);
        }
        $message->update(['isSeen' => 1]);

        return true;
    }
}
