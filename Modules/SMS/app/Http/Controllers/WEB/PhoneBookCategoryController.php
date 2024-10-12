<?php

namespace Modules\SMS\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Modules\SMS\Http\Requests\PhoneBookCategoryRequest;
use Modules\SMS\Services\PhoneBookCategoryService;
use RealRashid\SweetAlert\Facades\Alert;

class PhoneBookCategoryController extends Controller
{
    public function __construct(private readonly PhoneBookCategoryService $phoneBookCategoryService) {}

    public function index()
    {
        return view('sms::phone-book-category.index', [
            'phoneBookCategories' => $this->phoneBookCategoryService->getPhoneBookCategories(),
        ]);
    }

    public function store(PhoneBookCategoryRequest $request): Redirector|RedirectResponse
    {
        $this->phoneBookCategoryService->store($request->validated());

        Alert::toast('Phone Book has been create successfully.', 'success');

        return redirect('phone-book-category');
    }

    public function show(int $id): Factory|Redirector|RedirectResponse|View
    {
        $phoneBookCategory = $this->phoneBookCategoryService->findById($id);
        if (! $phoneBookCategory) {
            Alert::error('Error', _lang('Something went wrong. Phone Book not found.'));

            return redirect('phone-book-category');
        }

        return view('sms::phone-book-category.show', compact('phoneBookCategory'));
    }

    public function edit(int $id): Factory|Redirector|RedirectResponse|View
    {
        $phoneBookCategory = $this->phoneBookCategoryService->findById($id);
        if (! $phoneBookCategory) {
            Alert::error('Error', _lang('Something went wrong. Phone Book not found.'));

            return redirect('phone-book-category');
        }

        return view('sms::phone-book-category.edit', compact('phoneBookCategory'));
    }

    public function update(PhoneBookCategoryRequest $request, int $id): Redirector|RedirectResponse
    {
        $phoneBookCategory = $this->phoneBookCategoryService->findById($id);
        if (! $phoneBookCategory) {
            Alert::error('Error', _lang('Something went wrong. Phone Book not found.'));

            return redirect('phone-book-category');
        }
        $this->phoneBookCategoryService->update($request->validated(), $id);

        Alert::toast('Phone Book has been update successfully.', 'success');

        return redirect('phone-book-category');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $phoneBookCategory = $this->phoneBookCategoryService->findById($id);
        if (! $phoneBookCategory) {
            Alert::error('Error', _lang('Something went wrong. Phone Book not found.'));

            return redirect('phone-book-category');
        }
        $this->phoneBookCategoryService->delete($id);

        Alert::toast('Phone Book has been delete successfully.', 'success');

        return redirect('phone-book-category');
    }
}
