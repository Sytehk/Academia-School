<?php

namespace Modules\Finance\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Modules\Academic\Services\SectionService;
use Modules\Finance\Services\AbsentFineService;
use Modules\Finance\Services\FeeService;

class AmountConfigController extends Controller
{
    public function __construct(
        private readonly SectionService $sectionService,
        private readonly FeeService $feeService,
        private readonly AbsentFineService $absentFineService
    ) {}

    public function index(): View|Factory
    {
        return view('finance::amount_config.index', [
            'sections' => $this->sectionService->getSections(),
            'fees' => $this->feeService->getFees(),
            'absentFines' => $this->absentFineService->getAbsentFines(),
        ]);
    }
}
