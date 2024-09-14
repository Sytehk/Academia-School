<?php

namespace Modules\Teacher\Services;

use App\Services\AcademicContextService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Academic\Models\StudentSession;
use Modules\Academic\Models\Subject;
use Modules\Teacher\Http\Controllers\Concerns\ScopesTeacherResources;
use Modules\Teacher\Models\ClassLesson;
use Modules\Teacher\Models\ClassLessonAttachment;

/**
 * Lesson plans (class_lessons) and who may see or change them:
 *
 *  - a Teacher manages lessons they created or that target a subject + section
 *    assigned to them, and may only create lessons for those;
 *  - back-office staff (lesson_plan_manage) manage every lesson of the branch;
 *  - students and parents see only PUBLISHED lessons of the student's own
 *    class and section in the active session.
 */
class LessonPlanService
{
    use ScopesTeacherResources;

    public const UPLOAD_DIR = 'uploads/files/class_lessons/';

    /** Fields a client may set on a lesson. */
    private const FIELDS = [
        'title', 'topic', 'sub_topic', 'note', 'objectives', 'activities', 'evaluation',
        'date', 'term_number', 'week_number', 'duration_minutes', 'class_id', 'section_id', 'subject_id',
    ];

    public function __construct(
        private readonly AcademicContextService $academicContext,
    ) {}

    public function isTeacher(): bool
    {
        return auth()->user()?->user_type === 'Teacher';
    }

    /** Lessons the current staff member / teacher may manage. */
    public function manageable(): Builder
    {
        $query = ClassLesson::query();
        if (! $this->isTeacher()) {
            return $query;
        }

        $userId = auth()->id();
        $pairs = $this->assignedSubjectSectionPairs();

        return $query->where(function (Builder $q) use ($userId, $pairs) {
            $q->where('created_by', $userId);
            foreach ($pairs as [$subjectId, $sectionId]) {
                $q->orWhere(fn (Builder $inner) => $inner->where('subject_id', $subjectId)->where('section_id', $sectionId));
            }
        });
    }

    /** Published lessons visible to one student (own class/section, active session). */
    public function visibleTo(StudentSession $session): Builder
    {
        return ClassLesson::query()
            ->where('status', ClassLesson::STATUS_PUBLISHED)
            ->where('session_id', $session->session_id)
            ->where('class_id', $session->class_id)
            ->where('section_id', $session->section_id);
    }

    public function filter(Builder $query, array $filters, int $perPage = 20): LengthAwarePaginator
    {
        foreach (['class_id', 'section_id', 'subject_id', 'term_number', 'week_number', 'status'] as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                $query->where($column, $filters[$column]);
            }
        }
        if (! empty($filters['search'])) {
            $query->where(fn ($q) => $q->where('title', 'like', '%'.$filters['search'].'%')->orWhere('topic', 'like', '%'.$filters['search'].'%'));
        }

        return $query->with(['attachments', 'subject:id,subject_name', 'class:id,class_name', 'section:id,section_name'])
            ->orderByDesc('term_number')
            ->orderByDesc('week_number')
            ->orderByDesc('id')
            ->paginate(min(100, max(1, $perPage)));
    }

    /**
     * @param  array<int, UploadedFile>  $files
     *
     * @throws ValidationException
     */
    public function create(array $data, array $files = []): ClassLesson
    {
        $this->assertContext((int) $data['class_id'], (int) $data['section_id'], isset($data['subject_id']) ? (int) $data['subject_id'] : null);

        return DB::transaction(function () use ($data, $files) {
            $status = ($data['status'] ?? ClassLesson::STATUS_PUBLISHED) === ClassLesson::STATUS_DRAFT
                ? ClassLesson::STATUS_DRAFT
                : ClassLesson::STATUS_PUBLISHED;

            $lesson = ClassLesson::create(array_intersect_key($data, array_flip(self::FIELDS)) + [
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'session_id' => $this->academicContext->currentSessionId(),
                'status' => $status,
                'published_at' => $status === ClassLesson::STATUS_PUBLISHED ? now() : null,
                'created_by' => auth()->id(),
            ]);

            $this->attach($lesson, $files);

            return $lesson->fresh('attachments');
        });
    }

    /**
     * @param  array<int, UploadedFile>  $files
     *
     * @throws ValidationException
     */
    public function update(ClassLesson $lesson, array $data, array $files = []): ClassLesson
    {
        $classId = (int) ($data['class_id'] ?? $lesson->class_id);
        $sectionId = (int) ($data['section_id'] ?? $lesson->section_id);
        $subjectId = array_key_exists('subject_id', $data) ? ($data['subject_id'] ? (int) $data['subject_id'] : null) : ($lesson->subject_id ? (int) $lesson->subject_id : null);
        $this->assertContext($classId, $sectionId, $subjectId);

        return DB::transaction(function () use ($lesson, $data, $files) {
            $lesson->update(array_intersect_key($data, array_flip(self::FIELDS)));
            $this->attach($lesson, $files);

            return $lesson->fresh('attachments');
        });
    }

    public function setPublished(ClassLesson $lesson, bool $published): ClassLesson
    {
        $lesson->update([
            'status' => $published ? ClassLesson::STATUS_PUBLISHED : ClassLesson::STATUS_DRAFT,
            'published_at' => $published ? ($lesson->published_at ?? now()) : null,
        ]);

        return $lesson->fresh('attachments');
    }

    public function delete(ClassLesson $lesson): void
    {
        DB::transaction(function () use ($lesson) {
            foreach ($lesson->attachments as $attachment) {
                $this->removeFile($attachment->file);
            }
            $lesson->delete();
        });
    }

    public function deleteAttachment(ClassLessonAttachment $attachment): void
    {
        $this->removeFile($attachment->file);
        $attachment->delete();
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    private function attach(ClassLesson $lesson, array $files): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }
            $original = $file->getClientOriginalName();
            $mime = $file->getClientMimeType();
            $size = $file->getSize();
            $stored = secure_public_move($file, self::UPLOAD_DIR);

            ClassLessonAttachment::create([
                'institute_id' => $lesson->institute_id,
                'branch_id' => $lesson->branch_id,
                'class_lesson_id' => $lesson->id,
                'file' => $stored,
                'original_name' => mb_substr($original, 0, 255),
                'mime' => $mime,
                'size' => $size,
            ]);
        }
    }

    private function removeFile(string $file): void
    {
        $path = base_path('public/'.self::UPLOAD_DIR.basename($file));
        if (is_file($path)) {
            @unlink($path);
        }
    }

    /**
     * @throws ValidationException
     */
    private function assertContext(int $classId, int $sectionId, ?int $subjectId): void
    {
        $this->academicContext->assertClassSectionGroup($classId, $sectionId);

        if ($subjectId && ! Subject::where('id', $subjectId)->where('class_id', $classId)->exists()) {
            throw ValidationException::withMessages(['subject_id' => [_lang('The selected subject does not belong to the chosen class.')]]);
        }

        if ($this->isTeacher() && (! $subjectId || ! $this->teacherTeaches($classId, $sectionId, $subjectId))) {
            throw ValidationException::withMessages(['subject_id' => [_lang('You can only create lessons for a subject and section assigned to you.')]]);
        }
    }
}
