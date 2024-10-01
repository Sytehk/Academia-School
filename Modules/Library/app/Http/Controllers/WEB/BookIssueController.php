<?php

namespace Modules\Library\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\LibraryMember;
use Modules\Library\Models\Book;
use Modules\Library\Models\BookIssue;
use Modules\Library\Models\LibraryFine;
use RealRashid\SweetAlert\Facades\Alert;

class BookIssueController extends Controller
{
    public function index(Request $request, $library_id = ''): Factory|View
    {
        $issues = [];
        $member = '';
        $library_id = $request->library_id;
        $library_members = LibraryMember::join('users', 'users.id', '=', 'library_members.user_id')->orderBy('library_members.library_id', 'asc')->get();

        $member = LibraryMember::join('users', 'users.id', '=', 'library_members.user_id')->where('library_members.library_id', $library_id)->first();
        $issues = BookIssue::select('*', 'book_issues.id AS id', 'book_issues.status AS issue_status')
            ->join('books', 'books.id', '=', 'book_issues.book_id')
            ->where('book_issues.status', 1)
            ->orderBy('book_issues.id', 'DESC');

        if ($library_id) {
            $issues->where('book_issues.library_id', $library_id);
        }
        $issues = $issues->get();

        return view('library::issues.issue-index', compact('issues', 'member', 'library_id', 'library_members'));
    }

    public function create(): Factory|View
    {
        return view('library::issues.issue-add', [
            'library_members' => LibraryMember::join('users', 'users.id', '=', 'library_members.user_id')->orderBy('library_members.library_id', 'asc')->get(),
            'books' => Book::all(),
        ]);
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $toDay = Carbon::now()->toDateString();
        $request->validate([
            'library_id' => 'required',
            'book_id' => 'required',
            'due_date' => 'required|date|date_format:"Y-m-d"|after:'.$toDay,
        ]);

        $libraryID = $request->library_id;
        $type = LibraryMember::where('library_members.library_id', $libraryID)->first()->member_type;
        $issue = new BookIssue;
        $issue->library_id = $request->library_id;
        $issue->book_id = $request->book_id;
        $issue->note = $request->note;
        $issue->issue_date = $toDay;
        $issue->due_date = $request->due_date;

        if ($type == 'Student') {
            $id = LibraryMember::where('library_members.member_type', $type)->where('library_members.library_id', $libraryID)->first()->student_id;
            $issue->student_id = $id;
            $issue->type = $type;
        } elseif ($type == 'Teacher') {
            $id = LibraryMember::where('library_members.member_type', $type)->where('library_members.library_id', $libraryID)->first()->teacher_id;
            $issue->teacher_id = $id;
            $issue->type = $type;
        } else {
            $id = LibraryMember::where('library_members.member_type', $type)->where('library_members.library_id', $libraryID)->first()->staff_id;
            $issue->staff_id = $id;
            $issue->type = $type;
        }

        $issue->save();

        Alert::toast(_lang('Book Issue create successfully'), 'success');

        return redirect('bookissues-list/'.$request->library_id);
    }

    public function multipleBookIssue(Request $request): Redirector|RedirectResponse
    {
        // Get the arrays of book_ids and return_dates
        $bookIds = $request->book_ids;
        $returnDates = $request->return_dates;

        $libraryID = $request->search_library_id;
        $type = LibraryMember::where('library_members.library_id', $libraryID)->first()->member_type;
        $userId = LibraryMember::where('library_members.library_id', $libraryID)->first()->user_id;
        $toDay = Carbon::now()->toDateString();

        // Loop through each pair of book_id and return_date
        foreach ($bookIds as $index => $bookId) {
            // Create a new book issue instance
            $issue = new BookIssue;
            $issue->library_id = $libraryID;
            $issue->user_id = $userId;
            $issue->book_id = $bookId;
            $issue->issue_date = $toDay;
            $issue->due_date = $returnDates[$index];

            if ($type == 'Student') {
                $id = LibraryMember::where('library_members.member_type', $type)
                    ->where('library_members.library_id', $libraryID)
                    ->first()->student_id;
                $issue->student_id = $id;
            } elseif ($type == 'Teacher') {
                $id = LibraryMember::where('library_members.member_type', $type)
                    ->where('library_members.library_id', $libraryID)
                    ->first()->teacher_id;
                $issue->teacher_id = $id;
            } else {
                $id = LibraryMember::where('library_members.member_type', $type)
                    ->where('library_members.library_id', $libraryID)
                    ->first()->staff_id;
                $issue->staff_id = $id;
            }

            $issue->type = $type;
            $issue->save();

            $bookData = Book::where('id', $bookId)->first();
            if ($bookData && isset($bookData->quantity) && is_numeric($bookData->quantity)) {
                $bookData->quantity = max(0, intval($bookData->quantity) - 1);
                $bookData->save();
            }
        }

        Alert::toast(_lang('Books Issue create successfully'), 'success');

        return redirect('books-ber-code-page');
    }

    public function show(int $id): Factory|View
    {
        $issue = BookIssue::select('*', 'users.name AS member_name', 'book_issues.status AS status')
            ->join('library_members', 'library_members.library_id', '=', 'book_issues.library_id')
            ->join('users', 'users.id', '=', 'library_members.user_id')
            ->join('books', 'books.id', '=', 'book_issues.book_id')
            ->where('book_issues.id', $id)
            ->first();

        return view('library::issues.issue-view', compact('issue'));
    }

    public function edit(int $id): Factory|View
    {
        $issue = BookIssue::find($id);

        return view('library::issues.issue-edit', compact('issue'));
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $request->validate([
            'library_id' => 'required',
            'book_id' => 'required',
            'due_date' => 'required',
        ]);

        $libraryID = $request->library_id;

        $type = LibraryMember::where('library_members.library_id', $libraryID)->first()->member_type;

        $issue = BookIssue::find($id);
        $issue->library_id = $request->library_id;
        $issue->book_id = $request->book_id;
        $issue->note = $request->note;
        $issue->due_date = $request->due_date;
        $issue->student_id = null;
        $issue->teacher_id = null;
        $issue->staff_id = null;

        if ($type == 'Student') {
            $id = LibraryMember::where('library_members.member_type', $type)->where('library_members.library_id', $libraryID)->first()->student_id;
            $issue->student_id = $id;
            $issue->type = $type;
        } elseif ($type == 'Teacher') {
            $id = LibraryMember::where('library_members.member_type', $type)->where('library_members.library_id', $libraryID)->first()->teacher_id;
            $issue->teacher_id = $id;
            $issue->type = $type;
        } else {
            $id = LibraryMember::where('library_members.member_type', $type)->where('library_members.library_id', $libraryID)->first()->staff_id;
            $issue->staff_id = $id;
            $issue->type = $type;
        }
        $issue->save();

        Alert::toast(_lang('Books Issue updated successfully'), 'success');

        return redirect('bookissues-list/'.$request->library_id);
    }

    public function book_return($id): bool
    {
        $toDay = Carbon::now()->toDateString();
        $issue = BookIssue::find($id);
        $issue->return_date = $toDay;
        $issue->status = 2;
        $issue->save();

        return true;
    }

    public function bookIssueMultipleReturn(Request $request): Redirector|RedirectResponse
    {
        $toDay = Carbon::now()->toDateString();
        // Loop through each book ID
        foreach ($request->book_issues_ids as $key => $book_issues_id) {

            $issue = BookIssue::find($book_issues_id);

            // If book issue exists, update return date and status
            if ($issue) {
                $issue->return_date = $toDay;
                $issue->status = 2;
                $issue->save();

                $bookData = Book::where('id', $issue->book_id)->first();
                if ($bookData && isset($bookData->quantity) && is_numeric($bookData->quantity)) {
                    $bookData->quantity = max(0, intval($bookData->quantity) + 1);
                    $bookData->save();
                }

                // Retrieve fine and lost book information from the request
                $fine = $request->fines[$key];
                $lost_book = $request->lost_books[$key];

                if ($fine && $lost_book) {
                    // Save fine and lost book information
                    $libraryFine = new LibraryFine;
                    $libraryFine->book_issue_id = $issue->id;
                    $libraryFine->library_id = $issue->library_id;
                    $libraryFine->fine = $fine;
                    $libraryFine->lost_book = $lost_book;
                    $libraryFine->total_fine = $fine + $lost_book;
                    $libraryFine->save();
                }
            }
        }

        Alert::toast(_lang('Books Issue returned successfully'), 'success');

        return redirect('/bookissues');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $issue = BookIssue::find($id);
        $issue->delete();

        Alert::toast(_lang('Books Issue deleted successfully'), 'success');

        return redirect('/bookissues');
    }

    // filter by all
    public function allIssues(): Factory|View
    {
        $issues = DB::table('book_issues')
            ->select(
                'book_issues.id',
                'book_issues.type',
                'book_issues.issue_date',
                'book_issues.return_date',
                'book_issues.status',
                'book_issues.student_id',
                'book_issues.teacher_id as teacher_id',
                'book_issues.staff_id',
                'book_issues.library_id',
                'book_issues.due_date',
                'books.book_name',
                'books.code',
                'students.first_name as student_first_name',
                'students.last_name as student_last_name',
                'students.phone as student_phone',
                'teachers.name as teacher_name',
                'teachers.phone as teacher_phone',
                'staffs.name as staff_name',
                'staffs.phone as staff_phone',
                'library_fines.id as library_fine_id',
                'library_fines.total_fine',
            )
            ->leftJoin('books', 'book_issues.book_id', '=', 'books.id')
            ->leftJoin('library_fines', 'book_issues.id', '=', 'library_fines.book_issue_id')
            ->leftJoin('students', 'book_issues.student_id', '=', 'students.id')
            ->leftJoin('teachers', 'book_issues.teacher_id', '=', 'teachers.id')
            ->leftJoin('staffs', 'book_issues.staff_id', '=', 'staffs.id')
            ->get();

        $idSelect = '';

        return view('library::issues.issue_filter', compact('issues', 'idSelect'));
    }

    public function commonFilter($status, $id)
    {
        if (($status == 0 || $status == 1 || $status == 2 || $status == '') && $id == '' || $id == null) {
            $data = '';

            if ($status == 0) {
                $data = [1, 2];
            } elseif ($status == 1) {
                $data = [1, 1];
            } elseif ($status == 2) {
                $data = [2, 2];
            } else {
                $data = [1, 2];
            }
            $issues = DB::table('book_issues')
                ->select(
                    'book_issues.id',
                    'book_issues.type',
                    'book_issues.issue_date',
                    'book_issues.return_date',
                    'book_issues.status',
                    'book_issues.student_id',
                    'book_issues.teacher_id',
                    'book_issues.staff_id',
                    'book_issues.library_id',
                    'book_issues.due_date',
                    'books.book_name',
                    'books.code',
                    'students.first_name as student_first_name',
                    'students.last_name as student_last_name',
                    'students.phone as student_phone',
                    'teachers.name as teacher_name',
                    'teachers.phone as teacher_phone',
                    'staffs.name as staff_name',
                    'staffs.phone as staff_phone',
                    'library_fines.id as library_fine_id',
                    'library_fines.total_fine',
                )
                ->leftJoin('library_fines', 'book_issues.id', '=', 'library_fines.book_issue_id')
                ->leftJoin('books', 'book_issues.book_id', '=', 'books.id')
                ->leftJoin('students', 'book_issues.student_id', '=', 'students.id')
                ->leftJoin('teachers', 'book_issues.teacher_id', '=', 'teachers.id')
                ->leftJoin('staffs', 'book_issues.staff_id', '=', 'staffs.id')
                ->whereIn('book_issues.status', $data)
                ->get();

            return $issues;
        } elseif (($status == 0 || $status == 1 || $status == 2 || $status == '') && $id) {
            $data = '';

            if ($status == 0) {
                $data = [1, 2];
            } elseif ($status == 1) {
                $data = [1, 1];
            } elseif ($status == 2) {
                $data = [2, 2];
            } else {
                $data = [1, 2];
            }
            $issues = DB::table('book_issues')
                ->select(
                    'book_issues.id',
                    'book_issues.type',
                    'book_issues.issue_date',
                    'book_issues.due_date',
                    'book_issues.return_date',
                    'book_issues.status',
                    'book_issues.student_id',
                    'book_issues.teacher_id',
                    'book_issues.staff_id',
                    'book_issues.library_id',
                    'books.code',
                    'books.book_name',
                    'students.first_name as student_first_name',
                    'students.last_name as student_last_name',
                    'students.phone as student_phone',
                    'teachers.name as teacher_name',
                    'teachers.phone as teacher_phone',
                    'staffs.name as staff_name',
                    'staffs.phone as staff_phone',
                    'library_fines.id as library_fine_id',
                    'library_fines.total_fine',
                )
                ->leftJoin('library_fines', 'book_issues.id', '=', 'library_fines.book_issue_id')
                ->leftJoin('books', 'book_issues.book_id', '=', 'books.id')
                ->leftJoin('students', 'book_issues.student_id', '=', 'students.id')
                ->leftJoin('teachers', 'book_issues.teacher_id', '=', 'teachers.id')
                ->leftJoin('staffs', 'book_issues.staff_id', '=', 'staffs.id')
                ->whereIn('book_issues.status', $data)
                ->where('book_issues.library_id', $id)
                ->get();

            return $issues;
        }
    }

    public function filterIssues(Request $request): Factory|View
    {
        $status = (int) $request->status_id;
        $user_id = (int) $request->user_id;

        if ($status == 0 && empty($user_id)) {
            $issues = $this->commonFilter($status, $user_id);
            $idSelect = 0;

            return view('library::issues.issue_filter', compact('issues', 'idSelect', 'user_id'));
        } elseif ($status == 0 && ! empty($user_id)) {
            $issues = $this->commonFilter($status, $user_id);
            $idSelect = 0;

            return view('library::issues.issue_filter', compact('issues', 'idSelect', 'user_id'));
        } elseif ($status == 1 && empty($user_id)) {
            $issues = $this->commonFilter($status, $user_id);
            $idSelect = 1;

            return view('library::issues.issue_filter', compact('issues', 'idSelect', 'user_id'));
        } elseif ($status == 1 && ! empty($user_id)) {
            $issues = $this->commonFilter($status, $user_id);
            $idSelect = 1;

            return view('library::issues.issue_filter', compact('issues', 'idSelect', 'user_id'));
        } elseif ($status == '2' && empty($user_id)) {
            $issues = $this->commonFilter($status, $user_id);
            $idSelect = 2;

            return view('library::issues.issue_filter', compact('issues', 'idSelect', 'user_id'));
        } elseif ($status == '2' && ! empty($user_id)) {
            $issues = $this->commonFilter($status, $user_id);
            $idSelect = 2;

            return view('library::issues.issue_filter', compact('issues', 'idSelect', 'user_id'));
        } elseif (empty($status) && ! empty($user_id)) {
            $issues = $this->commonFilter($status = '', $user_id);
            $idSelect = '';

            return view('library::issues.issue_filter', compact('issues', 'idSelect', 'user_id'));
        } else {
            $issues = $this->commonFilter('', '');
            $idSelect = '';

            return view('library::issues.issue_filter', compact('issues', 'idSelect', 'user_id'));
        }
    }

    public function issueReport(Request $request): Factory|View
    {
        $status = (int) $request->status_id;
        $user_id = (int) $request->user_id;

        if ($status == 0 && empty($user_id)) {
            $issues = $this->commonFilter($status, $user_id);
            $idSelect = 0;

            return view('library::issues.issue_filter_pdf', compact('issues', 'idSelect', 'user_id'));
        } elseif ($status == 0 && ! empty($user_id)) {
            $issues = $this->commonFilter($status, $user_id);
            $idSelect = 0;

            return view('library::issues.issue_filter_pdf', compact('issues', 'idSelect', 'user_id'));
        } elseif ($status == 1 && empty($user_id)) {
            $issues = $this->commonFilter($status, $user_id);
            $idSelect = 1;

            return view('library::issues.issue_filter_pdf', compact('issues', 'idSelect', 'user_id'));
        } elseif ($status == 1 && ! empty($user_id)) {
            $issues = $this->commonFilter($status, $user_id);
            $idSelect = 1;

            return view('library::issues.issue_filter_pdf', compact('issues', 'idSelect', 'user_id'));
        } elseif ($status == '2' && empty($user_id)) {
            $issues = $this->commonFilter($status, $user_id);
            $idSelect = 2;

            return view('library::issues.issue_filter_pdf', compact('issues', 'idSelect', 'user_id'));
        } elseif ($status == '2' && ! empty($user_id)) {
            $issues = $this->commonFilter($status, $user_id);
            $idSelect = 2;

            return view('library::issues.issue_filter_pdf', compact('issues', 'idSelect', 'user_id'));
        } elseif (empty($status) && ! empty($user_id)) {
            $issues = $this->commonFilter($status = '', $user_id);
            $idSelect = '';

            return view('library::issues.issue_filter_pdf', compact('issues', 'idSelect', 'user_id'));
        } else {
            $issues = $this->commonFilter('', '');
            $idSelect = '';

            return view('library::issues.issue_filter_pdf', compact('issues', 'idSelect', 'user_id'));
        }
    }
}
