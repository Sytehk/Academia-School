<?php

namespace Modules\LayoutCert\Services;

use App\Services\AcademicContextService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Academic\Models\StudentSession;
use Modules\LayoutCert\Http\Controllers\WEB\LayoutCertController;
use Modules\LayoutCert\Models\CertificateIssue;
use Modules\LayoutCert\Models\CertificateTemplate;

/**
 * Issues certificates for one or many students.
 *
 * A template's `body` (merge-field HTML, e.g. "...student {{student_name}}...")
 * is used when set; otherwise the type's existing hardcoded wording (the
 * *-format.blade.php views) renders exactly as before. Either way every issue
 * is recorded with a snapshot of the values printed, so the certificate can be
 * verified against what was actually issued even after the student record
 * changes or the template is edited later.
 */
class CertificateService
{
    public function __construct(
        private readonly AcademicContextService $academicContext,
    ) {}

    /**
     * @return array<string, string>
     */
    public function mergeFields(StudentSession $session): array
    {
        $student = $session->student;

        return [
            'student_name' => trim(($student->first_name ?? '').' '.($student->last_name ?? '')),
            'first_name' => $student->first_name ?? '',
            'last_name' => $student->last_name ?? '',
            'father_name' => $student->father_name ?? '',
            'mother_name' => $student->mother_name ?? '',
            'registration_no' => $student->registration_no ?? '',
            'admission_no' => $student->registration_no ?? '',
            'roll' => (string) ($session->roll ?? ''),
            'class_name' => $session->class?->class_name ?? '',
            'section_name' => $session->section?->section_name ?? '',
            'group_name' => $session->group?->group_name ?? '',
            'session_name' => $session->session?->session ?? '',
            'school_name' => (string) get_option('school_name'),
            'issue_date' => now()->format('d F Y'),
        ];
    }

    /**
     * @return array{issue: CertificateIssue, view: string, data: array}
     *
     * @throws ValidationException
     */
    public function issue(string $type, StudentSession $session, ?int $templateId = null, ?string $batchId = null): array
    {
        if (! array_key_exists($type, LayoutCertController::TYPES)) {
            throw ValidationException::withMessages(['type' => [_lang('Invalid certificate type.')]]);
        }
        if (! $session->student) {
            throw ValidationException::withMessages(['student_session_id' => [_lang('No student found for this session.')]]);
        }

        $template = $templateId
            ? CertificateTemplate::findOrFail($templateId)
            : CertificateTemplate::forType($type);

        $fields = $this->mergeFields($session);

        $issue = CertificateIssue::record($type, (int) $session->student_id, $fields['student_name'], $fields + [
            'type' => $type,
            'template_name' => $template->name,
        ], [
            'certificate_template_id' => $template->exists ? $template->id : null,
            'batch_id' => $batchId,
        ]);

        $verificationUrl = url('/certificate-verify/'.$issue->verification_code);

        $data = [
            'studentSession' => $session,
            'student' => $session->student,
            'template' => $template,
            'issue' => $issue,
            'verificationUrl' => $verificationUrl,
            'mergeFields' => $fields,
        ];

        return ['issue' => $issue, 'view' => $this->resolveView($type, $template), 'data' => $data + ['type' => $type]];
    }

    /**
     * @param  array<int, int>  $studentSessionIds
     * @return Collection<int, array{student_session_id: int, issue: CertificateIssue, view: string, data: array}|array{student_session_id: int, error: string}>
     */
    public function bulkIssue(string $type, array $studentSessionIds, ?int $templateId = null): Collection
    {
        $batchId = (string) Str::uuid();
        $sessions = StudentSession::with(['student', 'class', 'section', 'group', 'session'])
            ->whereIn('id', $studentSessionIds)
            ->get()
            ->keyBy('id');

        return collect($studentSessionIds)->map(function (int $id) use ($type, $templateId, $batchId, $sessions) {
            $session = $sessions->get($id);
            if (! $session) {
                return ['student_session_id' => $id, 'error' => _lang('Student session not found.')];
            }

            try {
                return ['student_session_id' => $id] + $this->issue($type, $session, $templateId, $batchId);
            } catch (ValidationException $e) {
                return ['student_session_id' => $id, 'error' => collect($e->errors())->flatten()->implode(' ')];
            }
        });
    }

    /** The Blade view for a rendered certificate: the editable body when the template has one, else the type's own hardcoded format. */
    public function resolveView(string $type, CertificateTemplate $template): string
    {
        if (filled($template->body)) {
            return 'layoutcert::layout_cert.custom-format';
        }

        return match ($type) {
            'general-certificate' => 'layoutcert::layout_cert.general-format',
            'testimonial' => 'layoutcert::layout_cert.testimonial-format',
            'attendance-certificate' => 'layoutcert::layout_cert.attendance-format',
            'transfer-certificate' => 'layoutcert::layout_cert.transfer-format',
            'hsc-recommendation', 'abroad-recommendation' => 'layoutcert::layout_cert.recommendation-format',
            'character-certificate' => 'layoutcert::layout_cert.character-format',
            'study-certificate' => 'layoutcert::layout_cert.study-format',
            'bonafide-certificate' => 'layoutcert::layout_cert.bonafide-format',
            'migration-certificate' => 'layoutcert::layout_cert.migration-format',
            default => 'layoutcert::layout_cert.general-format',
        };
    }

    /** Replaces {{token}} in a template body with the issue's snapshot values. */
    public function renderBody(string $body, array $fields): string
    {
        return preg_replace_callback('/\{\{\s*([a-z_]+)\s*\}\}/i', function ($match) use ($fields) {
            return e($fields[$match[1]] ?? '');
        }, $body);
    }

    /**
     * @throws ValidationException
     */
    public function ownedStudentSession(int $studentSessionId): StudentSession
    {
        $session = $this->academicContext->findOwnedStudentSession($studentSessionId);
        if (! $session) {
            throw ValidationException::withMessages(['student_session_id' => [_lang('The selected student session is invalid for your context.')]]);
        }

        return $session->loadMissing(['student', 'class', 'section', 'group', 'session']);
    }
}
