<?php

use Modules\Examination\Services\GradeCalculationService;
use Modules\Examination\Services\OnlineExamScoringService;
use Modules\Examination\Services\PositionCalculationService;

it('calculates percentage from obtained and full marks including decimals', function () {
    $service = new GradeCalculationService;

    expect($service->calculatePercentage(37.5, 50))->toBe(75.0)
        ->and($service->calculatePercentage(42, 50))->toBe(84.0)
        ->and($service->calculatePercentage(0, 100))->toBe(0.0)
        ->and($service->calculatePercentage(10, 0))->toBe(0.0);
});

it('ranks students with competition ranking and shared ties', function () {
    $service = new PositionCalculationService;
    $rows = collect([
        ['student_id' => 1, 'total_marks' => 90, 'gpa' => 5, 'status' => 'pass'],
        ['student_id' => 2, 'total_marks' => 80, 'gpa' => 4, 'status' => 'pass'],
        ['student_id' => 3, 'total_marks' => 80, 'gpa' => 4, 'status' => 'pass'],
        ['student_id' => 4, 'total_marks' => 70, 'gpa' => 3.5, 'status' => 'fail'],
    ]);

    $ranked = $service->rank($rows)->keyBy('student_id');

    expect($ranked[1]['position'])->toBe(1)
        ->and($ranked[2]['position'])->toBe(2)
        ->and($ranked[3]['position'])->toBe(2)
        ->and($ranked[4]['position'])->toBe(4);
});

it('can leave failed students unranked', function () {
    $service = new PositionCalculationService;
    $rows = collect([
        ['student_id' => 1, 'total_marks' => 90, 'gpa' => 5, 'status' => 'pass'],
        ['student_id' => 2, 'total_marks' => 40, 'gpa' => 1, 'status' => 'fail'],
    ]);

    $ranked = $service->rank($rows, PositionCalculationService::METHOD_TOTAL_MARKS, PositionCalculationService::FAILED_UNRANKED)->keyBy('student_id');

    expect($ranked[1]['position'])->toBe(1)
        ->and($ranked[2]['position'])->toBeNull();
});

it('converts online scores only when conversion is enabled', function () {
    $service = new OnlineExamScoringService;

    expect($service->convertScore(42, 50, 100, true))->toBe(84.0)
        ->and($service->convertScore(42, 50, 100, false))->toBe(42.0)
        ->and($service->convertScore(37.5, 50, 100, true))->toBe(75.0);
});
