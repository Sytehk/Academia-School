<?php

namespace Modules\Examination\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Modules\Examination\Models\TranscriptIssue;
use Modules\Examination\Services\AcademicRecordService;
use Modules\Examination\Services\ResultCardService;

/**
 * Multi-session transcripts, issued and QR-verifiable the same way as
 * certificates (see CertificateService): every issue snapshots the record at
 * that moment, so it stays checkable even after later results or promotions.
 */
class TranscriptController extends Controller
{
    public function __construct(
        private readonly AcademicRecordService $records,
        private readonly ResultCardService $cards,
        private readonly AcademicContextService $academicContext,
    ) {}

    public function issue(int $studentId): JsonResponse
    {
        $student = $this->academicContext->findOwnedStudent($studentId);
        abort_unless($student, 404);

        $record = $this->records->forStudent($student, false);
        $issue = TranscriptIssue::record($student->id, $record);

        return $this->responseSuccess([
            'issue' => $issue,
            'verification_url' => url('/certificate-verify/'.$issue->verification_code),
            'pdf_url' => route('api.transcripts.pdf', $issue->id),
        ], 'Transcript issued.');
    }

    public function pdf(int $issueId): Response
    {
        $issue = TranscriptIssue::findOrFail($issueId);

        return Pdf::loadView('examination::results.transcript_pdf', [
            'record' => $issue->snapshot,
            'issue' => $issue,
            'header' => $this->cards->schoolHeader(),
        ])->setPaper('a4', 'portrait')->download('transcript-'.$issue->transcript_number.'.pdf');
    }

    /** Public verification — matches the QR code printed on the transcript. */
    public function verify(string $code): JsonResponse
    {
        $issue = TranscriptIssue::withoutGlobalScope('tenant')->where('verification_code', strtoupper($code))->first();
        if (! $issue) {
            return $this->responseError([], _lang('Transcript not found.'), 404);
        }

        return $this->responseSuccess([
            'valid' => true,
            'transcript_number' => $issue->transcript_number,
            'student_name' => $issue->snapshot['student']['name'] ?? null,
            'issued_at' => $issue->issued_at,
        ], 'Transcript verified.');
    }
}
