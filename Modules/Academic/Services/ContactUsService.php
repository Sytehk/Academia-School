<?php

declare(strict_types=1);

namespace Modules\Academic\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Academic\Models\Contact;
use Modules\Academic\Repositories\ContactUsRepository;

class ContactUsService
{
    public function __construct(private readonly ContactUsRepository $contactUsRepository) {}

    public function getContactsMessage(): Collection
    {
        return $this->contactUsRepository->all();
    }

    public function store(array $data): ?Contact
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->contactUsRepository->create($data);
    }

    public function changeMessageStatus($id): bool
    {
        return $this->contactUsRepository->changeMessageStatus($id);
    }
}
