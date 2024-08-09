<?php

namespace Modules\QuestionBank\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\Authenticatable;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Modules\QuestionBank\Models\Question;
use Modules\QuestionBank\Models\QuestionBankSubject;
use Modules\QuestionBank\Repositories\QuestionRepository;
use Modules\QuestionBank\Services\QuestionSpreadsheetService;

class QuestionController extends Controller
{
    use Authenticatable, RequestSanitizerTrait;

    public function __construct(private QuestionRepository $questionRepository) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $search = strtolower($request->input('search'));
            $perPage = (int) $request->input('per_page', 20);
            $sortBy = $request->input('sort_by', 'id');
            $sortOrder = $request->input('sort_order', 'asc');
            $page = (int) $request->input('page', 1);

            $query = Question::query();

            // Apply simple filters
            $simpleFilters = [
                'question_category_id',
                'question_bank_class_id',
                'question_bank_group_id',
                'question_bank_subject_id',
                'question_bank_chapter_id',
                'question_bank_difficulty_level_id',
                'type',
                'status',
                'created_by',
            ];

            foreach ($simpleFilters as $filter) {
                if ($request->filled($filter)) {
                    $query->where($filter, $request->$filter);
                }
            }

            // Apply multiple select filters (array filters)
            $multiSelectFilters = [
                'types' => ['relation' => 'types', 'column' => 'question_type.question_bank_type_id'],
                'levels' => ['relation' => 'levels', 'column' => 'question_level.question_bank_level_id'],
                'topics' => ['relation' => 'topics', 'column' => 'question_topic.question_bank_topic_id'],
                'tags' => ['relation' => 'tags', 'column' => 'question_tag.question_bank_tag_id'],
                'sessions' => ['relation' => 'session', 'column' => 'question_session.question_bank_session_id'],
                'sources' => ['relation' => 'sources', 'column' => 'question_source.question_bank_source_id'],
                'sub_sources' => ['relation' => 'sub_sources', 'column' => 'question_sub_source.question_bank_sub_source_id'],
                'tests' => ['relation' => 'tests', 'column' => 'question_test.question_bank_test_id'],
            ];

            foreach ($multiSelectFilters as $input => $filter) {
                if ($request->filled($input)) {
                    $ids = json_decode($request->$input, true);
                    if (! empty($ids)) {
                        $query->whereHas($filter['relation'], function ($q) use ($filter, $ids) {
                            $q->whereIn($filter['column'], $ids);
                        });
                    }
                }
            }

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            // Eager load relationships
            $query->with([
                'questionCategory',
                'class',
                'session',
                'group',
                'subject',
                'chapter',
                'types',
                'tests',
                'levels',
                'topics',
                'sources',
                'sub_sources',
                'tags',
                'difficulty',
            ]);

            $allQuestions = $query->get();

            // Filter after loading if search is provided
            if (! empty($search)) {
                $allQuestions = $allQuestions->filter(function ($item) use ($search) {
                    // Decode JSON if it's Quill rich text format
                    $decoded = json_decode($item->question, true);
                    if (is_array($decoded)) {
                        $plainText = collect($decoded)->pluck('insert')->implode('');
                    } else {
                        $plainText = strip_tags($item->question);
                    }

                    return str_contains(strtolower($plainText), $search)
                        || str_contains(strtolower($item->question_name ?? ''), $search)
                        || str_contains(strtolower($item->explanation ?? ''), $search);
                });
            }

            // Paginate manually
            $paginated = new LengthAwarePaginator(
                $allQuestions->forPage($page, $perPage)->values(),
                $allQuestions->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            return $this->responseSuccess($paginated, 'Questions fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question_bank_class_id' => 'nullable|tenant_exists:question_bank_classes,id',
            'question_bank_group_id' => 'nullable|tenant_exists:question_bank_groups,id',
            'question_bank_subject_id' => 'required|tenant_exists:question_bank_subjects,id',
            'question_bank_chapter_id' => 'nullable|tenant_exists:question_bank_chapters,id',
            'question_bank_difficulty_level_id' => 'nullable|tenant_exists:question_bank_difficulty_levels,id',

            'question_name' => 'nullable|string|max:1000',
            'question' => 'required',
            'type' => ['required', Rule::in(QuestionSpreadsheetService::TYPES)],
            // Text-answer types have no options; short/descriptive answers are graded by hand.
            'options' => 'exclude_if:type,fill_blank,short_answer,descriptive|required|array|min:2|max:6',
            'options.*' => 'required|string|max:1000|distinct',
            'correct_answer' => 'exclude_if:type,short_answer,descriptive|required|array|min:1',
            'correct_answer.*' => 'required|string|max:1000',

            'explanation' => 'nullable',
            'marks' => 'nullable|integer|min:1',
            'negative_marks' => 'nullable|integer|min:0',
            'negative_marks_type' => ['nullable', Rule::in(['fixed', 'percentage'])],

            'image_file' => 'nullable|string|max:255',
            'question_level' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0|max:99999.9999',

            'language' => 'nullable|string|in:bn,en',
            'status' => ['nullable', Rule::in(['active', 'inactive', 'draft'])],
            'created_by' => 'nullable|exists:users,id',

            'types' => 'nullable|array',
            'types.*' => 'tenant_exists:question_bank_types,id',

            'tests' => 'nullable|array',
            'tests.*' => 'tenant_exists:question_bank_tests,id',

            'levels' => 'nullable|array',
            'levels.*' => 'tenant_exists:question_bank_levels,id',

            'topics' => 'nullable|array',
            'topics.*' => 'tenant_exists:question_bank_topics,id',

            'sources' => 'nullable|array',
            'sources.*' => 'tenant_exists:question_bank_sources,id',

            'sub_sources' => 'nullable|array',
            'sub_sources.*' => 'tenant_exists:question_bank_sub_sources,id',

            'tags' => 'nullable|array',
            'tags.*' => 'tenant_exists:question_bank_tags,id',

            'question_year' => 'nullable|array',
            'question_year.*.year' => 'required|digits:4',
            'question_year.*.board' => 'required|array|min:1',
            'question_year.*.board.*.board' => 'required|string|max:10',
            'question_year.*.board.*.desc' => 'nullable|string|max:255',
        ]);

        $plainText = $this->extractPlainTextFromQuill($validated['question']);
        $instituteId = $this->getCurrentInstituteId();
        $exists = Question::where('institute_id', $instituteId)
            ->get()
            ->filter(function ($q) use ($plainText) {
                $decoded = json_decode($q->question, true);

                $existingPlain = is_array($decoded)
                    ? collect($decoded)->pluck('insert')->implode('')
                    : strip_tags($q->question);

                return trim($existingPlain) === $plainText;
            })->isNotEmpty();
        if ($exists) {
            return $this->responseError([], 'This question already exists for the institute.', 422);
        }

        // Flatten question_years
        if ($request->filled('question_year')) {
            $flattenedYears = [];

            foreach ($request->input('question_year') as $item) {
                $year = $item['year'] ?? null;
                foreach ($item['board'] ?? [] as $board) {
                    $flattenedYears[] = [
                        'year' => $year,
                        'board' => $board['board'] ?? '',
                        'desc' => $board['desc'] ?? '',
                    ];
                }
            }

            $validated['question_year'] = $flattenedYears;
        }

        if (isset($validated['question_bank_subject_id'])) {
            $subject = QuestionBankSubject::find($validated['question_bank_subject_id']);
            $validated['question_category_id'] = $subject->question_category_id;
        }

        // Handle question type-specific logic
        $optionValues = array_values($validated['options'] ?? []);
        // Prepare data for insertion
        $validated['institute_id'] = $this->getCurrentInstituteId();
        $validated['options'] = json_encode($optionValues);
        $validated['correct_answer'] = json_encode($validated['correct_answer'] ?? []);
        if (isset($validated['question_year'])) {
            $validated['question_year'] = json_encode($validated['question_year']);
        }

        try {
            $question['question'] = json_encode($validated['question']);
            // Save main question
            $question = $this->questionRepository->create($validated);

            // Sync pivot relations if provided
            foreach (['types', 'tests', 'levels', 'topics', 'sources', 'sub_sources', 'tags'] as $relation) {
                if ($request->filled($relation) && method_exists($question, $relation)) {
                    $question->{$relation}()->sync($request->input($relation));
                }
            }

            return $this->responseSuccess($question, 'Question has been created successfully.');
        } catch (Exception $e) {
            return $this->responseError([], 'An error occurred while saving the question: '.$e->getMessage(), 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            // Fetch the question by ID
            $question = Question::find($id);

            // Check if the question exists
            if (! $question) {
                return $this->responseError([], 'Question Not Found', 404);
            }

            return $this->responseSuccess(
                $question,
                'Question has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $question = Question::find($id);
        if (! $question) {
            return $this->responseError([], 'Question Not Found', 404);
        }

        $validated = $request->validate([
            'question_bank_class_id' => 'nullable|tenant_exists:question_bank_classes,id',
            'question_bank_group_id' => 'nullable|tenant_exists:question_bank_groups,id',
            'question_bank_subject_id' => 'required|tenant_exists:question_bank_subjects,id',
            'question_bank_chapter_id' => 'nullable|tenant_exists:question_bank_chapters,id',
            'question_bank_difficulty_level_id' => 'nullable|tenant_exists:question_bank_difficulty_levels,id',

            'question_name' => 'nullable|string|max:1000',
            'question' => 'required',
            'type' => ['required', Rule::in(QuestionSpreadsheetService::TYPES)],
            // Text-answer types have no options; short/descriptive answers are graded by hand.
            'options' => 'exclude_if:type,fill_blank,short_answer,descriptive|required|array|min:2|max:6',
            'options.*' => 'required|string|max:1000|distinct',
            'correct_answer' => 'exclude_if:type,short_answer,descriptive|required|array|min:1',
            'correct_answer.*' => 'required|string|max:1000',

            'explanation' => 'nullable',
            'marks' => 'nullable|integer|min:1',
            'negative_marks' => 'nullable|integer|min:0',
            'negative_marks_type' => ['nullable', Rule::in(['fixed', 'percentage'])],

            'image_file' => 'nullable|string|max:255',
            'question_level' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0|max:99999.9999',

            'language' => 'nullable|string|in:bn,en',
            'status' => ['nullable', Rule::in(['active', 'inactive', 'draft'])],
            'created_by' => 'nullable|exists:users,id',

            'types' => 'nullable|array',
            'types.*' => 'tenant_exists:question_bank_types,id',

            'tests' => 'nullable|array',
            'tests.*' => 'tenant_exists:question_bank_tests,id',

            'levels' => 'nullable|array',
            'levels.*' => 'tenant_exists:question_bank_levels,id',

            'topics' => 'nullable|array',
            'topics.*' => 'tenant_exists:question_bank_topics,id',

            'sources' => 'nullable|array',
            'sources.*' => 'tenant_exists:question_bank_sources,id',

            'sub_sources' => 'nullable|array',
            'sub_sources.*' => 'tenant_exists:question_bank_sub_sources,id',

            'tags' => 'nullable|array',
            'tags.*' => 'tenant_exists:question_bank_tags,id',

            'question_year' => 'nullable|array',
            'question_year.*.year' => 'required|digits:4',
            'question_year.*.board' => 'required|array|min:1',
            'question_year.*.board.*.board' => 'required|string|max:10',
            'question_year.*.board.*.desc' => 'nullable|string|max:255',
        ]);

        if (isset($validated['question_bank_subject_id'])) {
            $subject = QuestionBankSubject::find($validated['question_bank_subject_id']);
            $validated['question_category_id'] = $subject->question_category_id;
        }

        // Flatten question_year
        if ($request->filled('question_year')) {
            $flattenedYears = [];
            foreach ($request->input('question_year') as $item) {
                $year = $item['year'] ?? null;
                foreach ($item['board'] ?? [] as $board) {
                    $flattenedYears[] = [
                        'year' => $year,
                        'board' => $board['board'] ?? '',
                        'desc' => $board['desc'] ?? '',
                    ];
                }
            }
            $validated['question_year'] = json_encode($flattenedYears);
        }

        // Handle type-specific validation
        $optionValues = array_values($validated['options'] ?? []);
        // Encode JSON fields
        $validated['options'] = json_encode($optionValues);
        $validated['correct_answer'] = json_encode($validated['correct_answer'] ?? []);

        try {
            $question['question'] = json_encode($validated['question']);
            $this->questionRepository->update($id, $validated);

            // Sync pivot relationships
            foreach (['types', 'tests', 'levels', 'topics', 'sources', 'sub_sources', 'tags'] as $relation) {
                if ($request->filled($relation) && method_exists($question, $relation)) {
                    $question->{$relation}()->sync($request->input($relation));
                }
            }

            return $this->responseSuccess($question, 'Question has been updated successfully.');
        } catch (Exception $e) {
            return $this->responseError([], 'An error occurred while updating the question: '.$e->getMessage(), 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $question = Question::find($id);

            if (! $question) {
                return $this->responseError([], 'Question Not Found', 404);
            }

            // Detach all pivot relationships if they exist
            foreach (['types', 'levels', 'topics', 'sources', 'sub_sources', 'tags'] as $relation) {
                if (method_exists($question, $relation)) {
                    $question->{$relation}()->detach();
                }
            }

            // Now delete the question
            $this->questionRepository->delete($id);

            return $this->responseSuccess([], 'Question has been deleted successfully.');
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode() ?: 500);
        }
    }

    protected function extractPlainTextFromQuill($quillJson): string
    {
        $decoded = is_string($quillJson) ? json_decode($quillJson, true) : $quillJson;

        if (is_array($decoded)) {
            return trim(collect($decoded)->pluck('insert')->implode(''));
        }

        return trim(strip_tags((string) $quillJson));
    }
}
