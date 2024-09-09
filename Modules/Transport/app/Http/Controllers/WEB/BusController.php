<?php

namespace Modules\Transport\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Modules\Transport\Http\Requests\Bus\BusStoreRequest;
use Modules\Transport\Http\Requests\Bus\BusUpdateRequest;
use Modules\Transport\Repositories\BusRepository;

class BusController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(
        private BusRepository $bus
    ) {}

    /**
     * Bus List
     */
    public function index()
    {
        try {

            $buses = $this->bus->getAll(request()->all());

            return view('transport::bus.index', compact('buses'));
        } catch (Exception $exception) {

            return back()
                ->with('error', $exception->getMessage());
        }
    }

    /**
     * Create Page
     */
    public function create()
    {

        return view(
            'transport::bus.create'
        );
    }

    /**
     * Store Bus
     */
    public function store(BusStoreRequest $request)
    {

        try {

            $this->bus->create(
                $request->validated()
            );

            return redirect()
                ->route('buses.index')
                ->with(
                    'success',
                    'Bus created successfully.'
                );
        } catch (Exception $exception) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    $exception->getMessage()
                );
        }
    }

    /**
     * Edit Page
     */
    public function edit(int $id)
    {

        try {

            $bus = $this->bus->getById($id);

            if (! $bus) {

                return redirect()
                    ->route('buses.index')
                    ->with(
                        'error',
                        'Bus not found.'
                    );
            }

            return view(
                'transport::bus.edit',
                compact('bus')
            );
        } catch (Exception $exception) {

            return back()
                ->with(
                    'error',
                    $exception->getMessage()
                );
        }
    }

    /**
     * Update Bus
     */
    public function update(
        BusUpdateRequest $request,
        int $id
    ) {

        try {

            $this->bus->update(
                $id,
                $this->getUpdateRequest($request)
            );

            return redirect()
                ->route('buses.index')
                ->with(
                    'success',
                    'Bus updated successfully.'
                );
        } catch (Exception $exception) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    $exception->getMessage()
                );
        }
    }

    /**
     * Delete Bus
     */
    public function destroy(int $id)
    {

        try {

            $bus = $this->bus->getById($id);

            if (! $bus) {

                return redirect()
                    ->route('buses.index')
                    ->with(
                        'error',
                        'Bus not found.'
                    );
            }

            $this->bus->delete($id);

            return redirect()
                ->route('buses.index')
                ->with(
                    'success',
                    'Bus deleted successfully.'
                );
        } catch (Exception $exception) {

            return back()
                ->with(
                    'error',
                    $exception->getMessage()
                );
        }
    }
}
