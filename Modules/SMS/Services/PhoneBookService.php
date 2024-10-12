<?php

declare(strict_types=1);

namespace Modules\SMS\Services;

use App\Services\AcademicContextService;
use App\Services\NotificationDispatchService;
use Modules\Academic\Models\StudentSession;
use Modules\SMS\Models\PhoneBook;
use Modules\SMS\Models\PhoneBookCategory;
use Modules\SMS\Repositories\PhoneBookRepository;

class PhoneBookService
{
    public function __construct(
        private readonly PhoneBookRepository $phoneBookRepository,
        private readonly AcademicContextService $academicContext,
        private readonly NotificationDispatchService $dispatcher,
    ) {}

    /**
     * Sync every current-session student's guardian contact into the Phone Book (FEAT-007).
     * Keyed by student_id so re-running this is idempotent — an existing student's row is
     * updated in place (picking up a class/section change or a corrected phone number)
     * rather than duplicated.
     *
     * @return array{synced: int, skipped: int}
     */
    public function syncFromStudents(): array
    {
        $instituteId = get_institute_id();
        $branchId = get_branch_id();

        $categoryId = PhoneBookCategory::where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->where('name', 'Student')
            ->value('id');

        $sessions = $this->academicContext
            ->applySessionScope($this->academicContext->applyTenantScope(StudentSession::query()))
            ->with('student', 'class', 'section')
            ->get();

        $synced = 0;
        $skipped = 0;

        foreach ($sessions as $session) {
            $student = $session->student;
            $phone = $this->dispatcher->studentPhone($student);

            if (! $student || ! $phone) {
                $skipped++;

                continue;
            }

            PhoneBook::updateOrCreate(
                [
                    'institute_id' => $instituteId,
                    'branch_id' => $branchId,
                    'student_id' => $student->id,
                ],
                [
                    'name' => trim(($student->first_name ?? '').' '.($student->last_name ?? '')),
                    'phone' => $phone,
                    'category_id' => $categoryId,
                    'class_id' => $session->class_id,
                    'section_id' => $session->section_id,
                    'source' => 'student',
                ]
            );

            $synced++;
        }

        return ['synced' => $synced, 'skipped' => $skipped];
    }

    public function getPhoneBooks(array $filter = [])
    {
        return $this->phoneBookRepository->all($filter);
    }

    public function store(array $data): ?PhoneBook
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->phoneBookRepository->create($data);
    }

    public function update(array $data, $id): ?bool
    {
        return $this->phoneBookRepository->update($data, $id);
    }

    public function findById($id): ?PhoneBook
    {
        return $this->phoneBookRepository->show((int) $id);
    }

    public function delete($id): ?bool
    {
        return $this->phoneBookRepository->delete($id);
    }
}
