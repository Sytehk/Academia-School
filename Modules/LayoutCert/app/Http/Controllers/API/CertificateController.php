<?php

namespace Modules\LayoutCert\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Modules\LayoutCert\Http\Controllers\WEB\LayoutCertController;
use Modules\LayoutCert\Models\CertificateIssue;
use Modules\LayoutCert\Models\CertificateTemplate;
use Modules\LayoutCert\Services\CertificateService;

/**
 * Certificate issuance API: single or bulk issue, PDF download, and public
 * QR verification as JSON (for a verification app/kiosk).
 */
class CertificateController extends Controller
{
    /** Keeps a bulk issue within a reasonable request size. */
    private const MAX_BULK = 200;

    public function __construct(
        private readonly CertificateService $certificates,
        private readonly AcademicContextService $academicContext,
    ) {}

    public function types(): JsonResponse
    {
        return $this->responseSuccess(LayoutCertController::TYPES, 'Certificate types fetched.');
    }

    public function templates(Request $request): JsonResponse
    {
        return $this->responseSuccess(
            CertificateTemplate::query()
                ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
                ->where('is_active', true)
                ->get(['id', 'name', 'type', 'orientation']),
            'Certificate templates fetched.'
        );
    }

    public function issue(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'string', Rule::in(array_keys(LayoutCertController::TYPES))],
            'student_session_id' => 'required|integer',
            'template_id' => ['nullable', 'integer', $this->academicContext->tenantExists('certificate_templates')],
        ]);

        $this->authorizeType($data['type']);
        $session = $this->certificates->ownedStudentSession((int) $data['student_session_id']);
        $result = $this->certificates->issue($data['type'], $session, $data['template_id'] ?? null);

        return $this->responseSuccess($this->present($result), 'Certificate issued.');
    }

    public function bulkIssue(Request $request): JsonResponse
    {
        abort_unless(can_access_system('layouts_certificates_bulk_issue'), 403);

        $data = $request->validate([
            'type' => ['required', 'string', Rule::in(array_keys(LayoutCertController::TYPES))],
            'class_id' => 'required|integer',
            'section_id' => 'nullable|integer',
            'template_id' => ['nullable', 'integer', $this->academicContext->tenantExists('certificate_templates')],
            'student_session_ids' => 'nullable|array|max:'.self::MAX_BULK,
            'student_session_ids.*' => 'integer',
        ]);

        $this->authorizeType($data['type']);
        $this->academicContext->assertClassSectionGroup((int) $data['class_id'], $data['section_id'] ?? null);

        $ids = $data['student_session_ids'] ?? $this->academicContext
            ->studentSessionQuery((int) $data['class_id'], $data['section_id'] ?? null)
            ->pluck('id')
            ->all();

        if (count($ids) > self::MAX_BULK) {
            return $this->responseError([], _lang('At most').' '.self::MAX_BULK.' '._lang('students can be issued a certificate in one batch.'), 422);
        }

        $results = $this->certificates->bulkIssue($data['type'], $ids, $data['template_id'] ?? null);
        $succeeded = $results->filter(fn ($row) => isset($row['issue']));

        return $this->responseSuccess([
            'batch_id' => $succeeded->first()['issue']->batch_id ?? null,
            'issued' => $succeeded->count(),
            'failed' => $results->count() - $succeeded->count(),
            'results' => $results->map(fn ($row) => isset($row['issue'])
                ? $this->present($row) + ['student_session_id' => $row['student_session_id']]
                : $row)->values(),
        ], 'Bulk certificate issue completed.');
    }

    /**
     * PDF for an EXISTING issue — renders from its stored snapshot, never
     * creates a new issue record, so re-downloading doesn't mint new numbers.
     */
    public function pdf(int $issueId): Response
    {
        $issue = CertificateIssue::findOrFail($issueId);
        $template = $issue->certificate_template_id
            ? CertificateTemplate::withTrashed()->find($issue->certificate_template_id)
            : null;
        $template ??= CertificateTemplate::forType($issue->type);

        $studentSession = $this->academicContext->studentSessionForStudent((int) $issue->student_id);
        $verificationUrl = url('/certificate-verify/'.$issue->verification_code);

        $data = [
            'studentSession' => $studentSession,
            'student' => $studentSession?->student,
            'template' => $template,
            'issue' => $issue,
            'verificationUrl' => $verificationUrl,
            'mergeFields' => (array) $issue->snapshot,
            'type' => $issue->type,
        ];

        return Pdf::loadView($this->certificates->resolveView($issue->type, $template), $data)
            ->setPaper($template->orientation === 'landscape' ? [0, 0, 842, 595] : 'a4', $template->orientation)
            ->download('certificate-'.$issue->certificate_number.'.pdf');
    }

    /** Public verification as JSON — no auth, matches the QR code printed on the certificate. */
    public function verify(string $code): JsonResponse
    {
        $issue = CertificateIssue::withoutGlobalScope('tenant')
            ->where('verification_code', strtoupper($code))
            ->first();

        if (! $issue) {
            return $this->responseError([], _lang('Certificate not found.'), 404);
        }

        return $this->responseSuccess([
            'valid' => true,
            'type' => $issue->type,
            'type_label' => LayoutCertController::TYPES[$issue->type] ?? $issue->type,
            'certificate_number' => $issue->certificate_number,
            'student_name' => $issue->subject_name,
            'issued_at' => $issue->issued_at,
            'snapshot' => $issue->snapshot,
        ], 'Certificate verified.');
    }

    /** Each certificate type has its own permission (matches the web sidebar's per-type gating). */
    private function authorizeType(string $type): void
    {
        $permission = 'layouts_certificates_'.str_replace('-', '_', match ($type) {
            'general-certificate' => 'general',
            'hsc-recommendation' => 'hsc_recommendation_letter',
            'abroad-recommendation' => 'abroad_letter',
            'transfer-certificate' => 'transfer',
            'attendance-certificate' => 'attendance_certificate',
            default => str_replace('-certificate', '', $type),
        });

        abort_unless(can_access_system($permission), 403);
    }

    private function present(array $result): array
    {
        return [
            'issue' => $result['issue'],
            'certificate_number' => $result['issue']->certificate_number,
            'verification_url' => $result['data']['verificationUrl'],
            'pdf_url' => route('api.certificates.pdf', $result['issue']->id),
        ];
    }
}
