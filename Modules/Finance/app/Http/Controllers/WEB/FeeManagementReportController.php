<?php

namespace Modules\Finance\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\StudentCollectionTrait;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\StudentSession;
use Modules\Academic\Repositories\SectionRepository;
use Modules\Finance\Models\Fee;
use Modules\Finance\Models\StudentCollection;
use Modules\Finance\Models\StudentCollectionDetailsSubHead;
use Pdf;

class FeeManagementReportController extends Controller
{
    use StudentCollectionTrait;

    public function __construct(
        private readonly SectionRepository $sectionRepository,
    ) {}

    public function getPaymentInfo(Request $request): Factory|View
    {
        [$fromDate, $toDate, $classId, $sectionId] = $this->resolveReportFilters($request);
        $sectionsData = $this->sectionsForClass($classId);
        $classes = $this->classesForBranch();

        $studentCollections = $this->paymentCollectionsQuery($request)->get();

        return view(
            'finance::fee_collection_report.payment-fee-info',
            [
                'studentCollections' => $studentCollections,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'classId' => $classId,
                'sectionId' => $sectionId,
                'sectionsData' => $sectionsData,
                'classes' => $classes,
            ]
        );
    }

    public function printPaymentInfo(Request $request): View
    {
        [$fromDate, $toDate, $classId, $sectionId] = $this->resolveReportFilters($request);

        $studentCollections = $this->paymentCollectionsQuery($request)->get();

        return view(
            'finance::fee_collection_report.payment-fee-info-print',
            [
                'studentCollections' => $studentCollections,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'classId' => $classId,
                'sectionId' => $sectionId,
            ]
        );
    }

    public function downloadPaymentInfoPdf(Request $request)
    {
        [$fromDate, $toDate, $classId, $sectionId] = $this->resolveReportFilters($request);

        $studentCollections = $this->paymentCollectionsQuery($request)->get();

        $pdf = Pdf::loadView(
            'finance::fee_collection_report.payment-fee-info-pdf',
            [
                'studentCollections' => $studentCollections,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'classId' => $classId,
                'sectionId' => $sectionId,
            ]
        )->setPaper('a4', 'landscape');

        return $pdf->download(
            'payment-fee-report-'.now()->format('Y-m-d').'.pdf'
        );
    }

    public function getUnpaidFeeInfo(Request $request): Factory|View
    {
        $sessionId = (int) get_option('academic_year');
        $classId = $request->filled('class_id') ? (int) $request->class_id : null;
        $sectionId = $request->filled('section_id') ? (int) $request->section_id : null;
        $classes = $this->classesForBranch();
        $sectionsData = $this->sectionsForClass($classId);
        $studentDatas = [];

        if ($request->filled('class_id') || $request->filled('section_id')) {
            $request->validate([
                'class_id' => 'required|exists:classes,id',
                'section_id' => 'required|exists:sections,id',
            ]);

            $students = $this->studentsForReport($sessionId, $classId, $sectionId);
            $studentDatas = $this->buildUnpaidStudentData($students, $sessionId);
        }

        return view(
            'finance::fee_collection_report.unpaid-info',
            [
                'studentDatas' => $studentDatas,
                'sectionsData' => $sectionsData,
                'classes' => $classes,
                'sessionId' => $sessionId,
                'classId' => $classId,
                'sectionId' => $sectionId,
            ]
        );
    }

    private function resolveReportFilters(Request $request): array
    {
        return [
            $request->from_date ?? Carbon::today()->format('Y-m-d'),
            $request->to_date ?? Carbon::today()->format('Y-m-d'),
            $request->filled('class_id') ? (int) $request->class_id : null,
            $request->filled('section_id') ? (int) $request->section_id : null,
        ];
    }

    private function paymentCollectionsQuery(Request $request): Builder
    {
        [$fromDate, $toDate, $classId, $sectionId] = $this->resolveReportFilters($request);

        return StudentCollection::query()
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->whereDate('invoice_date', '>=', $fromDate)
            ->whereDate('invoice_date', '<=', $toDate)
            ->when($classId, fn (Builder $query) => $query->where('class_id', $classId))
            ->when($sectionId, fn (Builder $query) => $this->applySectionFilter($query, $sectionId))
            ->with([
                'student.user',
                'student.studentSession',
                'details.feeHead',
                'details.subHeads.feeSubHead',
            ])
            ->latest();
    }

    private function studentsForReport(int $sessionId, ?int $classId, ?int $sectionId): Collection
    {
        return Student::query()
            ->select(
                'users.*',
                'student_sessions.roll',
                'classes.class_name',
                'sections.section_name',
                'students.id as id',
                'student_groups.group_name',
                'students.student_category_id',
                'students.status as student_status'
            )
            ->join('users', 'users.id', '=', 'students.user_id')
            ->join('student_sessions', 'students.id', '=', 'student_sessions.student_id')
            ->leftJoin('classes', 'classes.id', '=', 'student_sessions.class_id')
            ->leftJoin('sections', 'sections.id', '=', 'student_sessions.section_id')
            ->leftJoin('student_groups', 'student_sessions.group_id', '=', 'student_groups.id')
            ->where('users.user_type', 'Student')
            ->where('student_sessions.institute_id', get_institute_id())
            ->where('student_sessions.branch_id', get_branch_id())
            ->where('student_sessions.session_id', $sessionId)
            ->when($classId, fn (Builder $query) => $query->where('student_sessions.class_id', $classId))
            ->when($sectionId, fn (Builder $query) => $query->where('student_sessions.section_id', $sectionId))
            ->orderBy('student_sessions.roll')
            ->get();
    }

    private function buildUnpaidStudentData(Collection $students, int $sessionId): array
    {
        $studentDatas = [];

        foreach ($students as $sKey => $student) {
            $session = StudentSession::query()
                ->where('student_id', $student->id)
                ->where('session_id', $sessionId)
                ->where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->first();

            if (! $session) {
                continue;
            }

            $amountConfig = Fee::query()
                ->where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->where('section_id', $session->section_id)
                ->where('class_id', $session->class_id)
                ->with(['feeHead.feeSubHeads'])
                ->get();

            $feeHeads = [];
            $totalDueAmount = 0;

            foreach ($amountConfig as $config) {
                if ($config->feeHead) {
                    $feeHeads[$config->feeHead->id] = $config->feeHead;
                }
            }

            foreach ($feeHeads as $key => $feeHead) {
                $updatedFeeSubHeads = [];
                $amount = [];

                foreach ($feeHead->feeSubHeads as $feeSubHead) {
                    $studentCollectionDetailsSubHead = StudentCollectionDetailsSubHead::query()
                        ->where('student_id', $student->id)
                        ->where('institute_id', get_institute_id())
                        ->where('branch_id', get_branch_id())
                        ->where('session_id', $session->session_id)
                        ->where('fee_head_id', $feeHead->id)
                        ->where('sub_head_id', $feeSubHead->id)
                        ->first();

                    if (
                        ! $studentCollectionDetailsSubHead ||
                        optional($studentCollectionDetailsSubHead->collectionDetail)->total_paid == 0
                    ) {
                        $updatedFeeSubHeads[] = $feeSubHead;

                        $payableAmount = $this->getCollectionAmountsByFeeHeadAndSubHeads(
                            $student->id,
                            $feeHead->id,
                            [$feeSubHead->id]
                        );

                        $amount[$feeSubHead->id] = $payableAmount;
                        $totalDueAmount += $payableAmount['fee_and_fine_payable'] ?? 0;
                    }
                }

                $feeHeads[$key]->feeSubHeads = $updatedFeeSubHeads;
                $feeHeads[$key]->amount = $amount;
            }

            $studentDatas[$sKey] = $student;
            $studentDatas[$sKey]['feeHeads'] = $feeHeads;
            $studentDatas[$sKey]['total_paid_amount'] = $totalDueAmount;
        }

        return $studentDatas;
    }

    private function applySectionFilter(Builder $query, int $sectionId): Builder
    {
        $sessionId = (int) get_option('academic_year');

        return $query->whereIn('student_id', function ($subQuery) use ($sectionId, $sessionId) {
            $subQuery->select('student_id')
                ->from('student_sessions')
                ->where('section_id', $sectionId)
                ->where('session_id', $sessionId)
                ->where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id());
        });
    }

    private function classesForBranch(): Collection
    {
        return ClassModel::query()
            ->select('id', 'class_name')
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->orderBy('class_name')
            ->get();
    }

    private function sectionsForClass(?int $classId): Collection
    {
        if (! $classId) {
            return collect();
        }

        return $this->sectionRepository->getSectionsByClassId($classId);
    }
}
