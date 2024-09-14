<?php

namespace Modules\Teacher\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Teacher\Models\ClassLesson;
use Modules\Teacher\Models\ClassLessonAttachment;
use Modules\Teacher\Services\LessonPlanService;

/**
 * Lesson plans for the teacher portal (`/api/classlessons…`, own subjects and
 * sections only) and for back-office staff (`/api/lesson-plans…`, gated by
 * lesson_plan_manage). The legacy fields (title, note, date, file…file_4) keep
 * working; attachments may be sent as files[] as well.
 */
class ClassLessonController extends Controller
{
    private const FILE_RULE = 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,jpg,jpeg,png,gif,webp,zip,mp3,mp4|max:10240';

    public function __construct(private readonly LessonPlanService $lessons) {}

    public function index(Request $request): JsonResponse
    {
        return $this->responseSuccess(
            $this->lessons->filter($this->lessons->manageable(), $request->all(), (int) $request->input('per_page', 20)),
            'Lessons fetched successfully.'
        );
    }

    public function all(Request $request): JsonResponse
    {
        return $this->index($request);
    }

    public function show(int $id): JsonResponse
    {
        return $this->responseSuccess(
            $this->find($id)->load(['attachments', 'subject:id,subject_name', 'class:id,class_name', 'section:id,section_name']),
            'Lesson fetched successfully.'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request, false);

        return $this->responseSuccess($this->lessons->create($data, $this->files($request)), 'Lesson created successfully.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $lesson = $this->find($id);
        $data = $this->validated($request, true);

        return $this->responseSuccess($this->lessons->update($lesson, $data, $this->files($request)), 'Lesson updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->lessons->delete($this->find($id));

        return $this->responseSuccess([], 'Lesson deleted successfully.');
    }

    public function publish(int $id): JsonResponse
    {
        return $this->responseSuccess($this->lessons->setPublished($this->find($id), true), 'Lesson published.');
    }

    public function unpublish(int $id): JsonResponse
    {
        return $this->responseSuccess($this->lessons->setPublished($this->find($id), false), 'Lesson moved back to draft.');
    }

    public function addAttachments(Request $request, int $id): JsonResponse
    {
        $lesson = $this->find($id);
        $request->validate(['files' => 'required|array|min:1|max:10', 'files.*' => self::FILE_RULE]);

        return $this->responseSuccess($this->lessons->update($lesson, [], $this->files($request)), 'Attachments added.');
    }

    public function destroyAttachment(int $attachmentId): JsonResponse
    {
        $attachment = ClassLessonAttachment::findOrFail($attachmentId);
        $this->find((int) $attachment->class_lesson_id); // ownership check
        $this->lessons->deleteAttachment($attachment);

        return $this->responseSuccess([], 'Attachment deleted.');
    }

    private function find(int $id): ClassLesson
    {
        $lesson = $this->lessons->manageable()->whereKey($id)->first();
        abort_unless($lesson, 404, _lang('Lesson not found.'));

        return $lesson;
    }

    private function validated(Request $request, bool $partial): array
    {
        $req = fn (string $rule) => ($partial ? 'sometimes|' : '').$rule;

        return $request->validate([
            'title' => $req('required|string|max:191'),
            'topic' => 'nullable|string|max:255',
            'sub_topic' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:20000',
            'objectives' => 'nullable|string|max:20000',
            'activities' => 'nullable|string|max:20000',
            'evaluation' => 'nullable|string|max:20000',
            'date' => $req('required|date'),
            'term_number' => 'nullable|integer|min:1|max:4',
            'week_number' => 'nullable|integer|min:1|max:52',
            'duration_minutes' => 'nullable|integer|min:1|max:600',
            'class_id' => $req('required|integer'),
            'section_id' => $req('required|integer'),
            'subject_id' => 'nullable|integer',
            'status' => 'nullable|in:draft,published',
            'files' => 'nullable|array|max:10',
            'files.*' => self::FILE_RULE,
            'file' => 'nullable|'.self::FILE_RULE,
            'file_2' => 'nullable|'.self::FILE_RULE,
            'file_3' => 'nullable|'.self::FILE_RULE,
            'file_4' => 'nullable|'.self::FILE_RULE,
        ]);
    }

    /** files[] plus the legacy single-file fields. */
    private function files(Request $request): array
    {
        $files = (array) $request->file('files', []);
        foreach (['file', 'file_2', 'file_3', 'file_4'] as $legacy) {
            if ($request->hasFile($legacy)) {
                $files[] = $request->file($legacy);
            }
        }

        return $files;
    }
}
