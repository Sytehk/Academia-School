<?php

namespace Modules\Library\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Library\Models\Book;
use Modules\Library\Services\BookService;

class BookController extends Controller
{
    public function __construct(private readonly BookService $bookService) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        try {
            $books = Book::orderBy('id', 'DESC')->paginate($perPage);

            return $this->responseSuccess(
                $books,
                _lang('Books has been fetched successfully.')
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'datetime' => 'nullable|date',
            'code' => 'nullable|string|max:100',

            'book_group' => 'nullable|integer',
            'book_name' => 'required|string|max:255',
            'book_copy_no' => 'nullable|string|max:255',

            'publisher' => 'nullable|string|max:255',
            'publish_year' => 'nullable|string|max:20',
            'provider' => 'nullable|string|max:255',

            'total_page' => 'nullable|string|max:50',
            'identification_page' => 'nullable|string|max:255',
            'aa' => 'nullable|string|max:255',

            'author' => 'nullable|string|max:255',
            'edition' => 'nullable|string|max:255',

            'description' => 'nullable|string',

            'category' => 'nullable|string|max:255',
            'quantity' => 'nullable|integer',
            'price' => 'nullable|numeric',

            'bookself' => 'nullable|string|max:255',
            'rack' => 'nullable|string|max:255',

            'photo' => 'nullable|image|max:5120',
            'barcode' => 'nullable|image|max:5120',

            'status' => 'nullable|string|max:100',
        ]);

        $book = $this->bookService->createOrUpdateBook($validated);

        if (! $book) {
            return $this->responseError([], _lang('Something went wrong. Book can not be create.'));
        }

        return $this->responseSuccess([], _lang('Book has been create.'));
    }

    public function show(int $id): JsonResponse
    {
        $book = $this->bookService->findBookById($id);

        if (! $book) {
            return $this->responseError([], _lang('Something went wrong. Book can not be show.'));
        }

        return $this->responseSuccess($book, _lang('Book has been show.'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'datetime' => 'nullable|date',
            'code' => 'nullable|string|max:100',

            'book_group' => 'nullable|integer',
            'book_name' => 'required|string|max:255',
            'book_copy_no' => 'nullable|string|max:255',

            'publisher' => 'nullable|string|max:255',
            'publish_year' => 'nullable|string|max:20',
            'provider' => 'nullable|string|max:255',

            'total_page' => 'nullable|string|max:50',
            'identification_page' => 'nullable|string|max:255',
            'aa' => 'nullable|string|max:255',

            'author' => 'nullable|string|max:255',
            'edition' => 'nullable|string|max:255',

            'description' => 'nullable|string',

            'category' => 'nullable|string|max:255',
            'quantity' => 'nullable|integer',
            'price' => 'nullable|numeric',

            'bookself' => 'nullable|string|max:255',
            'rack' => 'nullable|string|max:255',

            'photo' => 'nullable|image|max:5120',
            'barcode' => 'nullable|image|max:5120',

            'status' => 'nullable|string|max:100',
        ]);

        $book = $this->bookService->findBookById($id);

        if (! $book) {
            return $this->responseError([], _lang('Something went wrong. Book can not be update.'));
        }

        $this->bookService->createOrUpdateBook($validated, $id);

        return $this->responseSuccess([], _lang('Book has been update.'));
    }

    public function destroy(int $id): JsonResponse
    {
        $book = $this->bookService->deleteBookById($id);

        if (! $book) {
            return $this->responseError([], _lang('Something went wrong. Book can not be delete.'));
        }

        return $this->responseSuccess([], _lang('Book has been delete.'));
    }
}
