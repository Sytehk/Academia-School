<?php

namespace Modules\Frontend\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Str;
use Modules\Frontend\Http\Requests\Page\PageStoreRequest;
use Modules\Frontend\Http\Requests\Page\PageUpdateRequest;
use Modules\Frontend\Models\Page;

class PageController extends Controller
{
    /**
     * INDEX
     */
    public function index()
    {
        try {

            $pages = Page::where([
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
            ])
                ->latest()
                ->paginate(20);

            return view('frontend::pages.index', compact('pages'));
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * CREATE
     */
    public function create()
    {
        return view('frontend::pages.create');
    }

    /**
     * STORE
     */
    public function store(PageStoreRequest $request)
    {

        try {

            $data = $request->validated();

            $data['institute_id'] = get_institute_id();
            $data['branch_id'] = get_branch_id();
            $data['author_id'] = getUserId();

            // slug generate

            $data['slug'] = ! empty($data['slug'])
                ? Str::slug($data['slug'])
                : Str::slug($data['title']);

            // duplicate check

            $exists = Page::where('institute_id', $data['institute_id'])
                ->where('branch_id', $data['branch_id'])
                ->where('slug', $data['slug'])
                ->exists();

            if ($exists) {

                return back()
                    ->withInput()
                    ->with('error', 'Slug already exists');
            }

            Page::create($data);

            return redirect()
                ->route('pages.index')
                ->with('success', 'Page created successfully');
        } catch (Exception $e) {

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * EDIT
     */
    public function edit($id)
    {

        try {

            $page = Page::where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->findOrFail($id);

            return view(
                'frontend::pages.edit',
                compact('page')
            );
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * UPDATE
     */
    public function update(PageUpdateRequest $request, $id)
    {

        try {

            $page = Page::where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->findOrFail($id);

            $data = $request->validated();

            if (empty($data['slug'])) {

                $data['slug'] = Str::slug($data['title']);
            } else {

                $data['slug'] = Str::slug($data['slug']);
            }

            $exists = Page::where('institute_id', $page->institute_id)
                ->where('branch_id', $page->branch_id)
                ->where('slug', $data['slug'])
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {

                return back()
                    ->withInput()
                    ->with('error', 'Slug already exists');
            }

            $page->update($data);

            return redirect()
                ->route('pages.index')
                ->with('success', 'Page updated successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * DELETE
     */
    public function destroy($id)
    {

        try {

            $page = Page::where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->findOrFail($id);

            $page->delete();

            return redirect()
                ->route('pages.index')
                ->with('success', 'Page deleted successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}
