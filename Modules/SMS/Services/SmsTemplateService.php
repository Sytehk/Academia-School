<?php

declare(strict_types=1);

namespace Modules\SMS\Services;

use Modules\SMS\Models\SmsTemplate;
use Modules\SMS\Repositories\SmsTemplateRepository;

class SmsTemplateService
{
    public function __construct(
        private readonly SmsTemplateRepository $smsTemplateRepository
    ) {}

    public function getSmsTemplates()
    {
        return $this->smsTemplateRepository->all();
    }

    public function store(array $data): ?SmsTemplate
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->smsTemplateRepository->create($data);
    }

    public function update(array $data, $id): ?bool
    {
        return $this->smsTemplateRepository->update($data, $id);
    }

    public function findById(int $id): ?SmsTemplate
    {
        return $this->smsTemplateRepository->show((int) $id);
    }

    public function delete($id): ?bool
    {
        return $this->smsTemplateRepository->delete($id);
    }
}
