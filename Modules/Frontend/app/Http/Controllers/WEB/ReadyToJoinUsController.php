<?php

namespace Modules\Frontend\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Modules\Frontend\Http\Requests\ReadyToJoinUs\ReadyToJoinUsStoreRequest;
use Modules\Frontend\Http\Requests\ReadyToJoinUs\ReadyToJoinUsUpdateRequest;
use Modules\Frontend\Repositories\ReadyToJoinUsRepository;

class ReadyToJoinUsController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(
        private ReadyToJoinUsRepository $readyToJoinUs
    ) {}

    /**
     * Ready To Join Us List Page
     */
    public function index()
    {
        try {

            $readyToJoinUs = $this->readyToJoinUs->getAll(request()->all());

            return view('frontend::ready_to_join_us.index', compact('readyToJoinUs'));
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Create Page
     */
    public function create()
    {
        return view('frontend::ready_to_join_us.create');
    }

    /**
     * Store Ready To Join Us
     */
    public function store(ReadyToJoinUsStoreRequest $request)
    {
        try {

            $this->readyToJoinUs->create($request->all());

            return redirect()
                ->route('ready-to-join-us.index')
                ->with('success', 'Ready To Join Us created successfully');
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

            $readyToJoin = $this->readyToJoinUs->getById($id);

            return view('frontend::ready_to_join_us.edit', compact('readyToJoin', 'id'));
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update Ready To Join Us
     */
    public function update(ReadyToJoinUsUpdateRequest $request, int $id)
    {
        try {

            $this->readyToJoinUs->update(
                $id,
                $this->getUpdateRequest($request)
            );

            return redirect()
                ->route('ready-to-join-us.index')
                ->with('success', 'Ready To Join Us updated successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete Ready To Join Us
     */
    public function destroy(int $id)
    {
        try {

            $readyToJoin = $this->readyToJoinUs->getById($id);

            if (! $readyToJoin) {

                return back()->with('error', 'Ready To Join Us Not Found');
            }

            $this->readyToJoinUs->delete($id);

            return redirect()
                ->route('ready-to-join-us.index')
                ->with('success', 'Ready To Join Us deleted successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}
