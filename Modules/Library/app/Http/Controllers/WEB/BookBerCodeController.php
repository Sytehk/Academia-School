<?php

namespace Modules\Library\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Academic\Models\LibraryMember;
use Modules\Library\Models\Book;

class BookBerCodeController extends Controller
{
    public function index(): Factory|View
    {
        return view('library::books.books_search', [
            'library_members' => LibraryMember::join('users', 'users.id', '=', 'library_members.user_id')->orderBy('library_id', 'asc')->get(),
            'books' => Book::all(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $searchBookCode = $request->search_books;

        if (strlen($searchBookCode) >= 6) {
            $searchBook = Book::where('code', $searchBookCode)->orderBy('code', 'asc')->first();
        } else {
            $searchBook = null;
        }

        return response()->json([
            'searchBook' => $searchBook,
        ]);
    }

    public function memberSearch(Request $request): JsonResponse
    {
        $searchMember = (int) $request->search_member;
        $library_member = LibraryMember::where('library_id', $searchMember)
            ->join('users', 'users.id', '=', 'library_members.user_id')
            ->select('users.id', 'users.name', 'users.email', 'users.phone', 'users.user_type', 'users.image', 'users.image', 'library_members.*')
            ->first();

        return response()->json([
            'library_member' => $library_member,
        ]);
    }

    public function printPage(): Factory|View
    {
        return view('library::books.print_books_search');
    }

    public function printBooks(Request $request): Factory|View
    {
        $fromBookCode = $request->input('books_code');
        $books = Book::whereIn('code', $fromBookCode)->select('id', 'code', 'book_name')->orderBy('code', 'asc')->get();

        return view('library::books.print_books_search_pdf', compact('books'));
    }
}
