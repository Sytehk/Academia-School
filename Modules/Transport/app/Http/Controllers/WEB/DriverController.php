<?php

namespace Modules\Transport\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Modules\Transport\Http\Requests\Driver\DriverStoreRequest;
use Modules\Transport\Http\Requests\Driver\DriverUpdateRequest;
use Modules\Transport\Models\Bus;
use Modules\Transport\Repositories\DriverRepository;

class DriverController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(
        private DriverRepository $driver
    ) {}

    /**
     * Driver List
     */
    public function index()
    {
        try {

            $drivers = $this->driver->getAll(
                request()->all()
            );

            return view('transport::driver.index', compact('drivers'));
        } catch (Exception $exception) {

            return back()
                ->with(
                    'error',
                    $exception->getMessage()
                );
        }
    }

    /**
     * Create Page
     */
    public function create()
    {
        $buses = Bus::get();

        return view('transport::driver.create', compact('buses'));
    }

    /**
     * Store Driver
     */
    public function store(
        DriverStoreRequest $request
    ) {

        try {

            $this->driver->create(
                $request->validated()
            );

            return redirect()
                ->route('drivers.index')
                ->with(
                    'success',
                    'Driver created successfully.'
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

            $driver = $this->driver->getById($id);

            if (! $driver) {

                return redirect()
                    ->route('drivers.index')
                    ->with(
                        'error',
                        'Driver not found.'
                    );
            }
            $buses = Bus::get();

            return view(
                'transport::driver.edit',
                compact('driver', 'buses')
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
     * Update Driver
     */
    public function update(
        DriverUpdateRequest $request,
        int $id
    ) {

        try {

            $this->driver->update(
                $id,
                $this->getUpdateRequest($request)
            );

            return redirect()
                ->route('drivers.index')
                ->with(
                    'success',
                    'Driver updated successfully.'
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
     * Delete Driver
     */
    public function destroy(int $id)
    {

        try {

            $driver = $this->driver->getById($id);

            if (! $driver) {

                return redirect()
                    ->route('drivers.index')
                    ->with(
                        'error',
                        'Driver not found.'
                    );
            }

            $this->driver->delete($id);

            return redirect()
                ->route('drivers.index')
                ->with(
                    'success',
                    'Driver deleted successfully.'
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
