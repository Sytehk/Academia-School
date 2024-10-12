<?php

namespace Modules\SMS\Http\Controllers\WEB;

use App\Exports\PhoneBookExport;
use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Academic\Services\ClassService;
use Modules\SMS\Http\Requests\PhoneBookRequest;
use Modules\SMS\Services\PhoneBookCategoryService;
use Modules\SMS\Services\PhoneBookService;
use RealRashid\SweetAlert\Facades\Alert;

class PhoneBookController extends Controller
{
    public function __construct(
        private readonly PhoneBookService $phoneBookService,
        private readonly PhoneBookCategoryService $phoneBookCategoryService,
        private readonly ClassService $classService,
        private readonly AcademicContextService $academicContext,
    ) {}

    public function index(Request $request): Factory|View
    {
        $filter = $request->only(['class_id', 'section_id']);

        return view('sms::phone-book.index', [
            'phoneBooks' => $this->phoneBookService->getPhoneBooks($filter),
            'phoneBookCategories' => $this->phoneBookCategoryService->getPhoneBookCategories(),
            'classes' => $this->classService->getClasses(),
            'sections' => ! empty($filter['class_id']) ? $this->academicContext->sectionsForClass((int) $filter['class_id']) : collect(),
            'filters' => $filter,
        ]);
    }

    public function sync(): RedirectResponse
    {
        $summary = $this->phoneBookService->syncFromStudents();

        Alert::toast(
            _lang('Synced').': '.$summary['synced'].'. '._lang('Skipped (no phone number)').': '.$summary['skipped'].'.',
            'success'
        );

        return redirect()->route('phone-book.index');
    }

    public function exportExcel(Request $request)
    {
        $filter = $request->only(['class_id', 'section_id']);
        $phoneBooks = $this->phoneBookService->getPhoneBooks($filter);

        return Excel::download(
            new PhoneBookExport($phoneBooks),
            'phone-book-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $filter = $request->only(['class_id', 'section_id']);
        $phoneBooks = $this->phoneBookService->getPhoneBooks($filter);

        $pdf = \Pdf::loadView('sms::phone-book.export-pdf', compact('phoneBooks'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('phone-book-'.now()->format('Y-m-d').'.pdf');
    }

    public function store(PhoneBookRequest $request): Redirector|RedirectResponse
    {
        $this->phoneBookService->store($request->validated());

        Alert::toast('Phone Book has been create successfully.', 'success');

        return redirect('phone-book');
    }

    public function show(int $id): Factory|Redirector|RedirectResponse|View
    {
        $phoneBook = $this->phoneBookService->findById($id);
        if (! $phoneBook) {
            Alert::error('Error', _lang('Something went wrong. Phone book not found.'));

            return redirect('phone-book');
        }

        return view('sms::phone-book.show', compact('phoneBook'));
    }

    public function edit(int $id): Factory|Redirector|RedirectResponse|View
    {
        $phoneBook = $this->phoneBookService->findById($id);
        $phoneBookCategories = $this->phoneBookCategoryService->getPhoneBookCategories();
        if (! $phoneBook) {
            Alert::error('Error', _lang('Something went wrong. Phone book not found.'));

            return redirect('phone-book');
        }

        return view(
            'sms::phone-book.edit',
            [
                'phoneBook' => $phoneBook,
                'phoneBookCategories' => $phoneBookCategories,
            ]
        );
    }

    public function update(PhoneBookRequest $request, int $id): Redirector|RedirectResponse
    {
        $phoneBook = $this->phoneBookService->findById($id);
        if (! $phoneBook) {
            Alert::error('Error', _lang('Something went wrong. Phone book not found.'));

            return redirect('phone-book');
        }
        $this->phoneBookService->update($request->validated(), $id);

        Alert::toast('Phone Book has been update successfully.', 'success');

        return redirect('phone-book');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $phoneBook = $this->phoneBookService->findById($id);
        if (! $phoneBook) {
            Alert::error('Error', _lang('Something went wrong. Phone book not found.'));

            return redirect('phone-book');
        }
        $this->phoneBookService->delete($id);

        Alert::toast('Phone Book has been delete successfully.', 'success');

        return redirect('phone-book');
    }
}
