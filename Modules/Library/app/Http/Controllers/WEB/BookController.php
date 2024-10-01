<?php

namespace Modules\Library\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Imports\LibraryBooksImport;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Library\DataTables\BookDataTable;
use Modules\Library\Models\Book;
use Modules\Library\Services\BookService;
use RealRashid\SweetAlert\Facades\Alert;

class BookController extends Controller
{
    public function __construct(private readonly BookService $bookService) {}

    public function index(BookDataTable $dataTable)
    {
        return $dataTable->render('library::books.index');
    }

    public function create(): Factory|View
    {
        return view('library::books.create');
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $validated = $request->validate([
            'book_name' => 'required|string|max:191',
            'code' => [
                'nullable',
                'string',
                'max:50',
            ],
            'author' => 'nullable',
            'publisher' => 'nullable',
            'quantity' => 'nullable',
            'photo' => 'nullable|image|max:5120',
            'barcode' => 'nullable|image|max:5120',
            'description' => 'nullable|string',
        ]);

        $book = $this->bookService->createOrUpdateBook($validated);

        if (! $book) {
            Alert::error('Error', _lang('Something went wrong. Book can not be submitted.'));

            return redirect('books');
        }

        Alert::toast(_lang('Book has been create successfully'), 'success');

        return redirect('books');
    }

    public function show(int $id): Factory|Redirector|RedirectResponse|View
    {
        $book = $this->bookService->findBookById($id);

        if (! $book) {
            Alert::error('Error', _lang('Something went wrong. Book can not be show.'));

            return redirect('books');
        }

        return view('library::books.show', [
            'book' => $book,
        ]);
    }

    public function edit(int $id): Factory|Redirector|RedirectResponse|View
    {
        $book = $this->bookService->findBookById($id);

        if (! $book) {
            Alert::error('Error', _lang('Something went wrong. Book can not be edit.'));

            return redirect('books');
        }

        return view('library::books.edit', [
            'book' => $book,
        ]);
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $validated = $request->validate([
            'book_name' => 'required|string|max:191',
            'code' => [
                'nullable',
                'string',
                Rule::unique('books', 'code')->ignore($id),
                'max:50',
            ],
            'author' => 'nullable',
            'publisher' => 'nullable',
            'quantity' => 'nullable',
            'photo' => 'nullable|image|max:5120',
            'barcode' => 'nullable|image|max:5120',
            'description' => 'nullable|string',
        ]);

        $book = $this->bookService->findBookById($id);

        if (! $book) {
            Alert::error('Error', _lang('Something went wrong. Book can not be update.'));

            return redirect('books');
        }

        $this->bookService->createOrUpdateBook($validated, $id);

        Alert::toast(_lang('Book has been update successfully'), 'success');

        return redirect('books');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $book = $this->bookService->deleteBookById($id);

        if (! $book) {
            Alert::error('Error', _lang('Something went wrong. Book can not be delete.'));

            return redirect('books');
        }

        Alert::toast(_lang('Book has been delete successfully'), 'success');

        return redirect('books');
    }

    public function bulkUpload(): Factory|View
    {
        return view('library::books.books-bulk-upload');
    }

    public function bulkStore(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'xlsx_file' => 'required|mimes:xlsx,csv',
        ]);

        $file = $request->file('xlsx_file');
        $import = new LibraryBooksImport;
        Excel::import($import, $file);
        $importedData = $import->data;

        DB::beginTransaction();
        try {
            foreach ($importedData as $row) {
                $book = new Book;
                $book->id = $row['id'];
                $book->code = $row['code'];
                $book->book_name = $row['name'];
                $book->author = $row['author'];
                $book->save();
            }

            DB::commit();

            Alert::toast(_lang('Book has been create successfully'), 'success');

            return redirect('books');
        } catch (Exception $e) {
            DB::rollback();

            Alert::error('Error', $e->getMessage());

            return back();
        }
    }

    public function downloadDemoFile()
    {
        $filePath = public_path('uploads/library_books_xlsx_file/library_books_demo.xlsx');

        if (file_exists($filePath)) {
            return response()->download($filePath, 'library_books_demo.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        abort(404, 'File not found');
    }
}
