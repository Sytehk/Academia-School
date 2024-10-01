<?php

namespace Modules\Library\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Academic\Models\LibraryMember;
use Modules\Library\Models\Book;
use Modules\Library\Models\BookIssue;
use Modules\Library\Models\LibraryFine;

class APILibraryController extends Controller
{
    public function index(Request $request, $library_id = ''): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;

        $issues = [];
        $library_id = intval($request->library_id);
        $issues = BookIssue::select('*', 'book_issues.id AS id', 'book_issues.status AS issue_status')
            ->join('books', 'books.id', '=', 'book_issues.book_id')
            ->where('book_issues.status', 1)
            ->orderBy('book_issues.id', 'DESC');

        if ($library_id) {
            $issues->where('book_issues.library_id', $library_id);
        }

        $issues = $issues->paginate($perPage);

        return $this->responseSuccess($issues, 'Books Issues has been fetched successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'book_ids' => 'required|array',
            'return_dates' => 'required|array',
            'library_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->responseError([], 'Book Issue In-Validated.', 422);
        }

        // Get the arrays of book_ids and return_dates
        $bookIds = $request->book_ids;
        $returnDates = $request->return_dates;
        $libraryID = intval($request->library_id);

        // Get the library member data, and check if it exists
        $libraryMember = LibraryMember::where('library_id', $libraryID)->first();
        if (! $libraryMember) {
            return $this->responseError([], 'Library member not found.', 404);
        }

        // Extract member type and user_id
        $type = $libraryMember->member_type;
        $userId = $libraryMember->user_id;
        $toDay = Carbon::now()->toDateString();

        try {
            // Start a database transaction
            DB::beginTransaction();

            // Loop through each pair of book_id and return_date
            foreach ($bookIds as $index => $bookId) {
                // Create a new book issue instance
                $issue = new BookIssue;
                $issue->institute_id = get_institute_id();
                $issue->branch_id = get_branch_id();
                $issue->library_id = $libraryID;
                $issue->user_id = $userId;
                $issue->book_id = $bookId;
                $issue->issue_date = $toDay;
                $issue->due_date = $returnDates[$index];

                // Set student_id, teacher_id, or staff_id based on member type
                switch ($type) {
                    case 'Student':
                        $id = $libraryMember->student_id;
                        $issue->student_id = $id;
                        break;
                    case 'Teacher':
                        $id = $libraryMember->teacher_id;
                        $issue->teacher_id = $id;
                        break;
                    case 'Staff':
                        $id = $libraryMember->staff_id;
                        $issue->staff_id = $id;
                        break;
                    default:
                        // Handle any other member type, or set a default value
                        break;
                }

                $issue->type = $type;
                $issue->save();

                // Update the book quantity
                $bookData = Book::find($bookId);
                if ($bookData && isset($bookData->quantity) && is_numeric($bookData->quantity)) {
                    $bookData->quantity = max(0, intval($bookData->quantity) - 1);
                    $bookData->save();
                }
            }

            // Commit the transaction if all operations were successful
            DB::commit();

            return $this->responseSuccess([], 'Books Issue successfully done.');
        } catch (Exception $e) {
            // Rollback the transaction if an error occurred
            DB::rollback();

            return $this->responseError([], $e->getMessage());
        }
    }

    public function bookReturn(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'bookIssues' => 'required|array',
            'bookIssues.*.book_issue_id' => 'required|integer',
            'bookIssues.*.fine' => 'required|numeric',
            'bookIssues.*.lost_books' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->responseError([], 'Attendance not found.', 404);
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            $toDay = Carbon::now()->toDateString();

            // Loop through each book issue
            foreach ($request->bookIssues as $issueData) {
                // Check if the book issue exists in the database
                $issue = BookIssue::find($issueData['book_issue_id']);

                if (! $issue) {
                    // Handle case where book issue is not found
                    return $this->responseError([], "Book issue with ID {$issueData['book_issue_id']} not found.", 404);
                }

                // Update return date and status
                $issue->return_date = $toDay;
                $issue->status = 2; // Assuming 2 indicates returned status
                $issue->save();

                // Update the book quantity
                $bookData = Book::where('id', $issue->book_id)->first(); // Adjusted to 'book_id' if needed
                if ($bookData && is_numeric($bookData->quantity)) {
                    $bookData->quantity = max(0, intval($bookData->quantity) + 1);
                    $bookData->save();
                }

                // Retrieve fine and lost book information from the issue data
                $fine = $issueData['fine'];
                $lost_book = $issueData['lost_books'];

                // Save fine and lost book information if applicable
                if ($fine > 0 || $lost_book > 0) {
                    $libraryFine = new LibraryFine;
                    $libraryFine->book_issue_id = $issue->id;
                    $libraryFine->library_id = $issue->library_id; // Ensure library_id is set correctly
                    $libraryFine->fine = $fine;
                    $libraryFine->lost_book = $lost_book;
                    $libraryFine->total_fine = $fine + $lost_book;
                    $libraryFine->save();
                }
            }

            // Commit the transaction
            DB::commit();

            return $this->responseSuccess([], 'Books have been returned successfully.');
        } catch (Exception $e) {
            // Rollback the transaction if an error occurred
            DB::rollback();

            return $this->responseError([], 'Error returning books: '.$e->getMessage());
        }
    }

    public function filterAllIssues(Request $request): JsonResponse
    {
        $status = $request->status_id; // Can be null, 0, 1, or 2
        $userId = $request->user_id; // Can be null or a specific ID
        $perPage = (int) ($request->per_page ?: 50);

        // Default query
        $query = DB::table('book_issues')
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
                'library_fines.total_fine'
            )
            ->leftJoin('books', 'book_issues.book_id', '=', 'books.id')
            ->leftJoin('library_fines', 'book_issues.id', '=', 'library_fines.book_issue_id')
            ->leftJoin('students', 'book_issues.student_id', '=', 'students.id')
            ->leftJoin('teachers', 'book_issues.teacher_id', '=', 'teachers.id')
            ->leftJoin('staffs', 'book_issues.staff_id', '=', 'staffs.id');

        // Apply filters
        if (! is_null($status)) {
            if ($status == 0) {
                $query->whereIn('book_issues.status', [1, 2]); // Pending
            } elseif (in_array($status, [1, 2])) {
                $query->where('book_issues.status', $status); // Specific status
            }
        }

        if (! empty($userId)) {
            $query->where(function ($q) use ($userId) {
                $q->where('book_issues.student_id', $userId)
                    ->orWhere('book_issues.teacher_id', $userId)
                    ->orWhere('book_issues.staff_id', $userId);
            });
        }

        // Paginate the results
        $issues = $query->paginate($perPage);

        // Determine selected status for the response
        $idSelect = $status ?? '';

        return $this->responseSuccess([
            'issues' => $issues,
            'idSelect' => $idSelect,
            'user_id' => $userId,
        ], 'Filter Issues book.');
    }
}
