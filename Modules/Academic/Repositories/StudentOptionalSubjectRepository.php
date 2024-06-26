<?php

namespace Modules\Academic\Repositories;

use App\Abstracts\EntityRepository;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Modules\Academic\Models\OptionalSubjectConfig;
use Modules\Academic\Models\StudentOptionalSubject;
use Modules\Academic\Models\StudentSession;
use Symfony\Component\HttpFoundation\Response;

class StudentOptionalSubjectRepository extends EntityRepository
{
    public string $table = StudentOptionalSubject::TABLE_NAME;

    protected array $fillableColumns = [
        'institute_id',
        'branch_id',
        'student_id',
        'student_session_id',
        'optional_subject_config_id',
        'subject_id',
        'mark',
    ];

    public function getAll(array $filterData = []): Paginator
    {
        $filter = $this->getFilterData($filterData);
        // getQuery() returns a plain query-builder instance, so Eloquent's
        // SoftDeletes global scope does not apply here — filter explicitly.
        $query = $this->getQuery()->whereNull("{$this->table}.deleted_at");

        if (! empty($filter['search'])) {
            $query = $this->filterSearchQuery($query, $filter['search']);
        }

        return $query->orderBy($filter['orderBy'], $filter['order'])->paginate($filter['perPage']);
    }

    /**
     * @throws Exception
     */
    public function getById(int $id, array $selects = ['*']): ?object
    {
        $record = StudentOptionalSubject::with('student', 'studentSession', 'config', 'subject')
            ->where('institute_id', $this->getCurrentInstituteId())
            ->where('branch_id', $this->getCurrentBranchId())
            ->find($id);

        if (! $record) {
            throw new Exception(
                $this->getExceptionMessage(static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE),
                Response::HTTP_NOT_FOUND
            );
        }

        return $record;
    }

    /**
     * @throws Exception
     */
    public function create(array $data): object
    {
        $studentSession = StudentSession::where('institute_id', $this->getCurrentInstituteId())
            ->where('branch_id', $this->getCurrentBranchId())
            ->find($data['student_session_id'] ?? null);

        if (! $studentSession) {
            throw new Exception('Student session not found.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $config = $this->requireConfigWithSubject($data['optional_subject_config_id'] ?? null, $data['subject_id'] ?? null);

        $this->assertNotDuplicate($studentSession->student_id, $data['optional_subject_config_id'], $data['subject_id']);
        $this->assertWithinSelectionLimit($studentSession->student_id, $config);

        $record = StudentOptionalSubject::create([
            'institute_id' => $this->getCurrentInstituteId(),
            'branch_id' => $this->getCurrentBranchId(),
            'student_id' => $studentSession->student_id,
            'student_session_id' => $studentSession->id,
            'optional_subject_config_id' => $data['optional_subject_config_id'],
            'subject_id' => $data['subject_id'],
            'mark' => $data['mark'] ?? 100,
        ]);

        return $this->getById($record->id);
    }

    /**
     * @throws Exception
     */
    public function update(int $id, array $data): ?object
    {
        $record = $this->requireRecord($id);

        $studentSessionId = $data['student_session_id'] ?? $record->student_session_id;
        $studentSession = StudentSession::where('institute_id', $this->getCurrentInstituteId())
            ->where('branch_id', $this->getCurrentBranchId())
            ->find($studentSessionId);

        if (! $studentSession) {
            throw new Exception('Student session not found.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $configId = $data['optional_subject_config_id'] ?? $record->optional_subject_config_id;
        $subjectId = $data['subject_id'] ?? $record->subject_id;

        $this->requireConfigWithSubject($configId, $subjectId);

        $duplicate = StudentOptionalSubject::where('id', '!=', $id)
            ->where('student_id', $studentSession->student_id)
            ->where('optional_subject_config_id', $configId)
            ->where('subject_id', $subjectId)
            ->exists();

        if ($duplicate) {
            throw new Exception('This student already has this optional subject recorded for this config.', Response::HTTP_CONFLICT);
        }

        $record->update([
            'student_id' => $studentSession->student_id,
            'student_session_id' => $studentSession->id,
            'optional_subject_config_id' => $configId,
            'subject_id' => $subjectId,
            'mark' => $data['mark'] ?? $record->mark,
        ]);

        return $this->getById($record->id);
    }

    /**
     * @throws Exception
     */
    private function requireRecord(int $id): StudentOptionalSubject
    {
        $record = StudentOptionalSubject::where('institute_id', $this->getCurrentInstituteId())
            ->where('branch_id', $this->getCurrentBranchId())
            ->find($id);

        if (! $record) {
            throw new Exception(
                $this->getExceptionMessage(static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE),
                Response::HTTP_NOT_FOUND
            );
        }

        return $record;
    }

    /**
     * @throws Exception
     */
    private function requireConfigWithSubject(?int $configId, ?int $subjectId): OptionalSubjectConfig
    {
        $config = OptionalSubjectConfig::where('institute_id', $this->getCurrentInstituteId())
            ->find($configId);

        if (! $config) {
            throw new Exception('Optional subject config not found.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! $subjectId || ! $config->items()->where('subject_id', $subjectId)->exists()) {
            throw new Exception('The selected subject is not part of this optional subject config.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $config;
    }

    /**
     * @throws Exception
     */
    private function assertNotDuplicate(int $studentId, int $configId, int $subjectId): void
    {
        $exists = StudentOptionalSubject::where('student_id', $studentId)
            ->where('optional_subject_config_id', $configId)
            ->where('subject_id', $subjectId)
            ->exists();

        if ($exists) {
            throw new Exception('This student already has this optional subject recorded for this config.', Response::HTTP_CONFLICT);
        }
    }

    /**
     * @throws Exception
     */
    private function assertWithinSelectionLimit(int $studentId, OptionalSubjectConfig $config): void
    {
        $pickCount = StudentOptionalSubject::where('student_id', $studentId)
            ->where('optional_subject_config_id', $config->id)
            ->count();

        if ($pickCount >= $config->selection_limit) {
            throw new Exception('This student has already reached the selection limit for this optional subject config.', Response::HTTP_CONFLICT);
        }
    }

    protected function getExceptionMessages(): array
    {
        return [
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'Student optional subject record does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'Student optional subject record could not be deleted.',
        ];
    }
}
