<?php

namespace Modules\Library\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Library\Services\BookCategoryService;
use RealRashid\SweetAlert\Facades\Alert;

class BookCategoriesController extends Controller
{
    public function __construct(private readonly BookCategoryService $bookCategoryService) {}

    public function index(): View|Factory
    {
        return view('library::categories.index', [
            'bookCategories' => $this->bookCategoryService->getBookCategories(),
        ]);
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:20',
        ]);

        $bookCategory = $this->bookCategoryService->createBookCategory($validated);

        if (! $bookCategory) {
            Alert::error('Error', _lang('Something went wrong. FeeHead can not be submitted.'));

            return redirect('book-categories');
        }

        Alert::toast(_lang('Book Category has been create successfully'), 'success');

        return redirect('book-categories');
    }

    public function edit(int $id): Factory|Redirector|RedirectResponse|View
    {
        $bookCategory = $this->bookCategoryService->findBookCategoryById($id);

        if (! $bookCategory) {
            Alert::error('Error', _lang('Something went wrong. FeeHead can not be edit.'));

            return redirect('book-categories');
        }

        return view('library::categories.edit', [
            'bookCategory' => $bookCategory,
        ]);
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:20',
        ]);

        $bookCategory = $this->bookCategoryService->findBookCategoryById($id);

        if (! $bookCategory) {
            Alert::error('Error', _lang('Something went wrong. FeeHead can not be update.'));

            return redirect('book-categories');
        }

        $this->bookCategoryService->updateBookCategory($validated, $id);

        Alert::toast(_lang('Book Category has been update successfully'), 'success');

        return redirect('book-categories');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $bookCategory = $this->bookCategoryService->deleteBookCategoryById($id);

        if (! $bookCategory) {
            Alert::error('Error', _lang('Something went wrong. FeeHead can not be delete.'));

            return redirect('book-categories');
        }

        Alert::toast(_lang('Book Category has been delete successfully'), 'success');

        return redirect('book-categories');
    }
}
