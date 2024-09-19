<?php

namespace Modules\Elearning\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Elearning\Http\Requests\Lesson\LessonStoreRequest;
use Modules\Elearning\Http\Requests\Lesson\LessonUpdateRequest;
use Modules\Elearning\Models\Lesson;
use Modules\Elearning\Repositories\LessonRepository;

class LessonController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private LessonRepository $lessonRepository) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->lessonRepository->getAll(request()->all()),
                'Lesson has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(LessonStoreRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // If visibility is password, hash it
            if (! empty($data['visibility']) && $data['visibility'] === 'password' && ! empty($data['password'])) {
                $data['password'] = $request->password;
            }

            // Normalize optional playback duration defaults
            $data['playback_hours'] = $data['playback_hours'] ?? 0;
            $data['playback_minutes'] = $data['playback_minutes'] ?? 0;
            $data['playback_seconds'] = $data['playback_seconds'] ?? 0;

            // Handle uploaded video if type is 'upload'
            if (
                isset($data['video_type']) &&
                $data['video_type'] === 'upload' &&
                $request->hasFile('uploaded_video_path')
            ) {
                $data['video_url'] = $request->file('uploaded_video_path'); // fileUploader will handle this
            }

            // Handle attachments
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('lessons/attachments', 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'url' => asset('storage/'.$path),
                        'mime' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                    ];
                }
            }

            $data['attachments'] = json_encode($attachments);

            // Pass to repository
            $lesson = $this->lessonRepository->create($data);

            return $this->responseSuccess($lesson, 'Lesson has been created successfully.');
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->lessonRepository->getById($id),
                'Lesson has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(LessonUpdateRequest $request, int $id): JsonResponse
    {
        $lesson = Lesson::where('id', $id)->first();
        try {
            $data = $request->validated();

            // If visibility is password, hash it
            if (! empty($data['visibility']) && $data['visibility'] === 'password' && ! empty($data['password'])) {
                $data['password'] = $request->password;
            }

            $data['playback_hours'] = $data['playback_hours'] ?? 0;
            $data['playback_minutes'] = $data['playback_minutes'] ?? 0;
            $data['playback_seconds'] = $data['playback_seconds'] ?? 0;

            if (
                isset($data['video_type']) &&
                $data['video_type'] === 'upload' &&
                $request->hasFile('uploaded_video_path')
            ) {
                $data['video_url'] = $request->file('uploaded_video_path');
            }

            // Handle attachments
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('lessons/attachments', 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'url' => asset('storage/'.$path),
                        'mime' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                    ];
                }
            }

            $data['attachments'] = json_encode($attachments);
            // Pass to repository
            $lesson = $this->lessonRepository->update($id, $data);

            return $this->responseSuccess($lesson, 'Lesson has been updated successfully.');
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->lessonRepository->delete($id),
                'Lesson has been deleted successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }
}
