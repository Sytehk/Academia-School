<?php

namespace Modules\Elearning\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Elearning\Http\Requests\Chapter\ChapterStoreRequest;
use Modules\Elearning\Http\Requests\Chapter\ChapterUpdateRequest;
use Modules\Elearning\Models\Chapter;
use Modules\Elearning\Models\Content;
use Modules\Elearning\Models\Lesson;
use Modules\Elearning\Repositories\ChapterRepository;
use Modules\QuestionBank\Models\Quiz;

class ChapterController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private ChapterRepository $chapterRepository) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->chapterRepository->getAll(request()->all()),
                'Chapter has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(ChapterStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->chapterRepository->create($request->all()),
                'Chapter has been created successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->chapterRepository->getById($id),
                'Chapter has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(ChapterUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->chapterRepository->update($id, $this->getUpdateRequest($request)),
                'Chapter has been updated successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->chapterRepository->delete($id),
                'Chapter has been deleted successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'chapter_id' => 'required|integer',
            'contents' => 'required|array|min:1',
            'contents.*.content_id' => 'required|integer|distinct',
            'contents.*.serial' => 'required|integer|min:0',
        ]);

        $chapterId = (int) $data['chapter_id'];
        // 1. Check if chapter exists
        $chapter = Chapter::find($chapterId);
        if (! $chapter) {
            return $this->responseError([], 'Chapter not found.', 404);
        }

        // 2. Collect all content IDs being updated
        $contentIds = collect($data['contents'])->pluck('content_id')->toArray();

        // 3. Fetch matching contents that belong to this chapter
        $existingContents = Content::whereIn('id', $contentIds)
            ->where('chapter_id', $chapterId)
            ->pluck('id')
            ->toArray();

        // 4. If some content IDs are invalid or don't belong to the chapter
        $invalidIds = array_diff($contentIds, $existingContents);
        if (! empty($invalidIds)) {
            return $this->responseError([], 'Some contents do not belong to the provided chapter.'.array_values($invalidIds), 422);
        }

        // 5. All good — proceed to update
        DB::transaction(function () use ($data, $chapterId) {
            foreach ($data['contents'] as $item) {
                Content::where('id', $item['content_id'])
                    ->where('chapter_id', $chapterId)
                    ->update(['serial' => $item['serial']]);
            }
        });

        return $this->responseSuccess(
            [],
            'Content order updated successfully.'
        );
    }

    public function contentDelete(Request $request)
    {
        $data = $request->validate([
            'chapter_id' => 'required|integer',
            'content_id' => 'required|integer',
        ]);

        $chapterId = (int) $data['chapter_id'];
        $contentId = (int) $data['content_id'];

        $chapter = Chapter::find($chapterId);
        if (! $chapter) {
            return $this->responseError([], 'Chapter not found.', 404);
        }

        $content = Content::where('id', $contentId)
            ->where('chapter_id', $chapterId)
            ->first();

        if (! $content) {
            return $this->responseError([], 'Content not found or does not belong to this chapter.', 404);
        }

        DB::transaction(function () use ($content) {
            // Delete the related lesson or quiz first
            if ($content->type === 'lesson') {
                Lesson::where('id', $content->type_id)->delete();
            } elseif ($content->type === 'quiz') {
                Quiz::where('id', $content->type_id)->delete();
            }

            // Then delete the content row
            $content->delete();
        });

        return $this->responseSuccess([], 'Content and associated data deleted successfully.');
    }

    public function chapterReorder(Request $request)
    {
        $data = $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
            'chapters' => 'required|array|min:1',
            'chapters.*.chapter_id' => 'required|integer|distinct|exists:chapters,id',
            'chapters.*.priority' => 'required|integer|min:0',
        ]);
        $courseId = $data['course_id'];
        // Validate that all chapter_ids belong to the given course
        $chapterIds = collect($data['chapters'])->pluck('chapter_id')->toArray();

        $validChapterIds = Chapter::whereIn('id', $chapterIds)
            ->where('course_id', $courseId)
            ->pluck('id')
            ->toArray();

        $invalidChapterIds = array_diff($chapterIds, $validChapterIds);
        if (! empty($invalidChapterIds)) {
            return $this->responseError([], 'Some chapters do not belong to the provided course.', 422);
        }

        // All good — proceed to update priorities
        DB::transaction(function () use ($data) {
            foreach ($data['chapters'] as $chapterData) {
                Chapter::where('id', $chapterData['chapter_id'])
                    ->update(['priority' => $chapterData['priority']]);
            }
        });

        return $this->responseSuccess(
            [],
            'Chapter priority order updated successfully.'
        );
    }
}
