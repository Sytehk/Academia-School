<?php

namespace Modules\Transport\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Modules\Transport\Http\Requests\BusRoute\BusRouteStoreRequest;
use Modules\Transport\Http\Requests\BusRoute\BusRouteUpdateRequest;
use Modules\Transport\Models\Bus;
use Modules\Transport\Repositories\BusRouteRepository;

class BusRouteController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(
        private BusRouteRepository $busRoute
    ) {}

    public function index()
    {
        try {
            $busRoutes = $this->busRoute->getAll(
                request()->all()
            );

            return view('transport::bus_route.index', compact('busRoutes'));
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
        $buses = Bus::all();

        return view('transport::bus_route.create', compact('buses'));
    }

    public function store(
        BusRouteStoreRequest $request
    ) {

        try {

            $this->busRoute->create(
                $request->validated()
            );

            return redirect()
                ->route('bus-routes.index')
                ->with(
                    'success',
                    'Bus Route created successfully.'
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

    public function show(int $id)
    {
        return $this->edit($id);
    }

    public function edit(int $id)
    {

        try {

            $route = $this->busRoute->getById($id);

            if (! $route) {

                return redirect()
                    ->route('bus-routes.index')
                    ->with(
                        'error',
                        'Bus Route not found.'
                    );
            }

            $buses = Bus::all();

            return view(
                'transport::bus_route.edit',
                compact('route', 'buses')
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
        BusRouteUpdateRequest $request,
        int $id
    ) {

        // try {

        $this->busRoute->update(
            $id,
            $this->getUpdateRequest($request)
        );

        return redirect()
            ->route('bus-routes.index')
            ->with(
                'success',
                'Bus Route updated successfully.'
            );
        // } catch (Exception $exception) {

        //     return back()
        //         ->withInput()
        //         ->with(
        //             'error',
        //             $exception->getMessage()
        //         );
        // }
    }

    public function destroy(int $id)
    {

        try {

            $route = $this->busRoute->getById($id);

            if (! $route) {

                return redirect()
                    ->route('bus-routes.index')
                    ->with(
                        'error',
                        'Bus Route not found.'
                    );
            }

            $this->busRoute->delete($id);

            return redirect()
                ->route('bus-routes.index')
                ->with(
                    'success',
                    'Bus Route deleted successfully.'
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
