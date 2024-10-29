<?php

namespace Modules\LayoutCert\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Academic\Services\ClassService;
use Modules\LayoutCert\Models\CertificateIssue;
use Modules\LayoutCert\Models\CertificateTemplate;
use RealRashid\SweetAlert\Facades\Alert;

class LayoutCertController extends Controller
{
    public const TYPES = [
        'general-certificate' => 'General Recommendation Letter',
        'testimonial' => 'Testimonial',
        'attendance-certificate' => 'Attendance Certificate',
        'transfer-certificate' => 'Transfer Certificate',
        'hsc-recommendation' => 'HSC Recommendation Letter',
        'abroad-recommendation' => 'Abroad Letter',
        'character-certificate' => 'Character Certificate',
        'study-certificate' => 'Study Certificate',
        'bonafide-certificate' => 'Bonafide Certificate',
        'migration-certificate' => 'Migration Certificate',
    ];

    public function __construct(
        private readonly ClassService $classService,
        private readonly AcademicContextService $academicContext,
    ) {}

    public function index(Request $request)
    {
        $type = $request->type;

        if (! array_key_exists($type, self::TYPES)) {
            Alert::error('Error', _lang('Invalid type'));

            return redirect('layout-cert');
        }

        $classes = $this->classService->getClasses();
        $typeLabel = self::TYPES[$type];

        return view('layoutcert::layout_cert.search', compact(
            'type',
            'typeLabel',
            'classes'
        ));
    }

    public function students(Request $request): string
    {
        $request->validate([
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
        ]);

        $classId = (int) $request->class_id;
        $sectionId = (int) $request->section_id;

        $this->academicContext->assertClassSectionGroup($classId, $sectionId);

        $sessions = $this->academicContext
            ->studentSessionQuery($classId, $sectionId)
            ->with('student')
            ->orderBy('roll')
            ->get();

        $output = '<option value="">'.e(_lang('Search by name or roll')).'</option>';

        foreach ($sessions as $row) {
            if (! $row->student || $row->roll === null || $row->roll === '') {
                continue;
            }

            $name = trim(($row->student->first_name ?? '').' '.($row->student->last_name ?? ''));
            $label = $name !== '' ? $name.' (Roll-'.$row->roll.')' : 'Roll-'.$row->roll;

            $output .= '<option value="'.e((string) $row->roll).'">'.e($label).'</option>';
        }

        return $output;
    }

    public function store(Request $request): Factory|Redirector|RedirectResponse|View
    {
        $request->validate([
            'type' => 'required|string|in:'.implode(',', array_keys(self::TYPES)),
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'roll' => 'required|string|max:15',
        ]);

        $this->academicContext->assertClassSectionGroup(
            (int) $request->class_id,
            (int) $request->section_id,
        );

        $studentSession = $this->academicContext
            ->studentSessionQuery((int) $request->class_id, (int) $request->section_id)
            ->with(['student', 'group', 'class', 'section', 'session'])
            ->where('roll', $request->roll)
            ->first();

        if (! $studentSession || ! $studentSession->student) {
            Alert::error('Error', _lang('No Student Found'));

            return redirect()->back();
        }

        $student = $studentSession->student;
        $type = $request->type;
        $template = CertificateTemplate::forType($type);

        $fullName = trim($student->first_name.' '.$student->last_name);

        $issue = CertificateIssue::record($type, $student->id, $fullName, [
            'name' => $fullName,
            'roll' => $studentSession->roll,
            'class' => $studentSession->class?->class_name,
            'section' => $studentSession->section?->section_name,
            'group' => $studentSession->group?->group_name,
        ]);

        $verificationUrl = url('/certificate-verify/'.$issue->verification_code);

        $viewData = compact('studentSession', 'student', 'template', 'issue', 'verificationUrl');

        return match ($type) {
            'general-certificate' => view('layoutcert::layout_cert.general-format', $viewData),
            'testimonial' => view('layoutcert::layout_cert.testimonial-format', $viewData),
            'attendance-certificate' => view('layoutcert::layout_cert.attendance-format', $viewData),
            'transfer-certificate' => view('layoutcert::layout_cert.transfer-format', $viewData),
            'hsc-recommendation', 'abroad-recommendation' => view('layoutcert::layout_cert.recommendation-format', $viewData + ['type' => $type]),
            'character-certificate' => view('layoutcert::layout_cert.character-format', $viewData),
            'study-certificate' => view('layoutcert::layout_cert.study-format', $viewData),
            'bonafide-certificate' => view('layoutcert::layout_cert.bonafide-format', $viewData),
            'migration-certificate' => view('layoutcert::layout_cert.migration-format', $viewData),
            default => redirect('layout-cert'),
        };
    }

    /**
     * Public certificate verification — no auth required, matches the QR code / code printed
     * on the certificate against a real issuance record rather than re-deriving data from
     * current student records (which would "verify" a certificate against data that has
     * since changed).
     */
    public function verify(string $code): View
    {
        $issue = CertificateIssue::withoutGlobalScope('tenant')
            ->where('verification_code', strtoupper($code))
            ->first();

        return view('layoutcert::layout_cert.verify', [
            'issue' => $issue,
            'typeLabel' => $issue ? (self::TYPES[$issue->type] ?? $issue->type) : null,
        ]);
    }
}
