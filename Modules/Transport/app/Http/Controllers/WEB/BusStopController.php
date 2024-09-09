<?php

namespace Modules\Transport\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Modules\Transport\Http\Requests\BusStop\BusStopStoreRequest;
use Modules\Transport\Http\Requests\BusStop\BusStopUpdateRequest;
use Modules\Transport\Models\BusRoute;
use Modules\Transport\Repositories\BusStopRepository;

class BusStopController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(
        private BusStopRepository $busStop
    ) {}

    public function index()
    {
        try {

            $stops = $this->busStop->getAll(
                request()->all()
            );

            return view('transport::bus_stop.index', compact('stops'));
        } catch (Exception $exception) {

            return back()
                ->with(
                    'error',
                    $exception->getMessage()
                );
        }
    }

    public function create()
    {
        $busRoutes = BusRoute::select('id', 'route_name')->get();

        return view('transport::bus_stop.create', compact('busRoutes'));
    }

    public function store(
        BusStopStoreRequest $request
    ) {

        try {

            $this->busStop->create(
                $request->validated()
            );

            return redirect()
                ->route('bus-stops.index')
                ->with(
                    'success',
                    'Bus Stop created successfully.'
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

    public function edit(int $id)
    {

        try {
            $stop = $this->busStop->getById($id);
            if (! $stop) {

                return redirect()
                    ->route('bus-stops.index')
                    ->with(
                        'error',
                        'Bus Stop not found.'
                    );
            }

            $busRoutes = BusRoute::select('id', 'route_name')->get();

            return view(
                'transport::bus-stop.edit',
                compact('stop', 'busRoutes')
            );
        } catch (Exception $exception) {

            return back()
                ->with(
                    'error',
                    $exception->getMessage()
                );
        }
    }

    public function update(
        BusStopUpdateRequest $request,
        int $id
    ) {

        try {

            $this->busStop->update(
                $id,
                $this->getUpdateRequest($request)
            );

            return redirect()
                ->route('bus-stops.index')
                ->with(
                    'success',
                    'Bus Stop updated successfully.'
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

    public function destroy(int $id)
    {
        try {
            $stop = $this->busStop->getById($id);
            if (! $stop) {
                return redirect()
                    ->route('bus-stops.index')
                    ->with(
                        'error',
                        'Bus Stop not found.'
                    );
            }

            $this->busStop->delete($id);

            return redirect()
                ->route('bus-stops.index')
                ->with(
                    'success',
                    'Bus Stop deleted successfully.'
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
