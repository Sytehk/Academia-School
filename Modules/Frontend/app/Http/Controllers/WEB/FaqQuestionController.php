<?php

namespace Modules\Frontend\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Modules\Frontend\Http\Requests\Faq\FaqStoreRequest;
use Modules\Frontend\Http\Requests\Faq\FaqUpdateRequest;
use Modules\Frontend\Repositories\FaqQuestionRepository;

class FaqQuestionController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(
        private FaqQuestionRepository $faqQuestion
    ) {}

    /**
     * FAQ List Page
     */
    public function index()
    {
        try {

            $faqQuestions = $this->faqQuestion->getAll(request()->all());

            return view('frontend::faqs.index', compact('faqQuestions'));
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Create Page
     */
    public function create()
    {
        return view('frontend::faqs.create');
    }

    /**
     * Store FAQ
     */
    public function store(FaqStoreRequest $request)
    {
        try {

            $this->faqQuestion->create($request->all());

            return redirect()
                ->route('faqs.index')
                ->with('success', 'FAQ created successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Edit Page
     */
    public function edit(int $id)
    {
        try {

            $faqQuestion = $this->faqQuestion->getById($id);

            return view('frontend::faqs.edit', compact('faqQuestion', 'id'));
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update FAQ
     */
    public function update(FaqUpdateRequest $request, int $id)
    {
        try {

            $this->faqQuestion->update(
                $id,
                $this->getUpdateRequest($request)
            );

            return redirect()
                ->route('faqs.index')
                ->with('success', 'FAQ updated successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete FAQ
     */
    public function destroy(int $id)
    {
        try {

            $faqQuestion = $this->faqQuestion->getById($id);

            if (! $faqQuestion) {

                return back()->with('error', 'FAQ Not Found');
            }

            $this->faqQuestion->delete($id);

            return redirect()
                ->route('faqs.index')
                ->with('success', 'FAQ deleted successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}
