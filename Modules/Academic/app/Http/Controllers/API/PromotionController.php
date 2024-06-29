<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Academic\Models\StudentPromotion;
use Modules\Academic\Models\StudentPromotionBatch;
use Modules\Academic\Services\PromotionService;

/**
 * Student promotion: preview (rule-based suggestions), confirm (with
 * per-student overrides), history, and revert.
 */
class PromotionController extends Controller
{
    public function __construct(private readonly PromotionService $promotions) {}

    public function preview(Request $request): JsonResponse
    {
        $data = $this->validatePlan($request, false);

        return $this->responseSuccess(
            $this->promotions->preview(
                (int) $data['from_session_id'],
                (int) $data['class_id'],
                $data['section_id'] ?? null,
                $data['rules'] ?? [],
                (bool) ($data['graduating'] ?? false),
            ),
            'Promotion preview generated.'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatePlan($request, true);

        $batch = $this->promotions->commit($data, $data['decisions'], auth()->id());

        return $this->responseSuccess($batch, 'Students promoted.');
    }

    public function index(Request $request): JsonResponse
    {
        return $this->responseSuccess(
            StudentPromotionBatch::when($request->filled('from_session_id'), fn ($q) => $q->where('from_session_id', (int) $request->from_session_id))
                ->latest('id')
                ->paginate((int) $request->input('per_page', 20)),
            'Promotion history fetched.'
        );
    }

    public function show(int $id): JsonResponse
    {
        return $this->responseSuccess(
            StudentPromotionBatch::with('promotions.student:id,first_name,last_name,registration_no')->findOrFail($id),
            'Promotion batch fetched.'
        );
    }

    public function revert(int $id): JsonResponse
    {
        $batch = StudentPromotionBatch::findOrFail($id);

        return $this->responseSuccess($this->promotions->revert($batch, auth()->id()), 'Promotion reverted.');
    }

    private function validatePlan(Request $request, bool $withDecisions): array
    {
        $rules = [
            'from_session_id' => 'required|integer|exists:academic_years,id',
            'class_id' => 'required|integer',
            'section_id' => 'nullable|integer',
            'graduating' => 'nullable|boolean',
            'rules' => 'nullable|array',
            'rules.basis' => ['nullable', Rule::in(['annual', 'exam', 'none'])],
            'rules.exam_id' => 'nullable|integer|tenant_exists:exams,id',
            'rules.min_average' => 'nullable|numeric|min:0|max:100',
            'rules.max_failed_subjects' => 'nullable|integer|min:0',
            'rules.require_pass' => 'nullable|boolean',
        ];

        if ($withDecisions) {
            $rules += [
                'to_session_id' => 'nullable|integer|exists:academic_years,id',
                'target_class_id' => 'nullable|integer',
                'target_section_id' => 'nullable|integer',
                'target_group_id' => 'nullable|integer',
                'roll_strategy' => ['nullable', Rule::in(['sequential', 'merit'])],
                'note' => 'nullable|string|max:1000',
                'decisions' => 'required|array|min:1',
                'decisions.*.student_id' => 'required|integer|distinct',
                'decisions.*.action' => ['required', Rule::in(StudentPromotion::ACTIONS)],
                'decisions.*.section_id' => 'nullable|integer',
                'decisions.*.roll' => 'nullable|string|max:20',
                'decisions.*.note' => 'nullable|string|max:255',
            ];
        }

        return $request->validate($rules);
    }
}
