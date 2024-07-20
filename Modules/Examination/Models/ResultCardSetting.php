<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ResultCardSetting extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'session_id',
        'result_title',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public const TEMPLATES = ['standard', 'nigerian'];

    public static function defaults(): array
    {
        return [
            'show_photo' => true,
            'show_position' => true,
            'show_gpa' => true,
            'show_percentage' => true,
            'show_failed_subjects' => true,
            'show_attendance' => true,
            'show_remarks' => true,
            'show_cognitive' => true,
            'show_affective' => true,
            'show_psychomotor' => true,
            'show_parent_signature' => true,
            'show_teacher_signature' => true,
            'show_principal_signature' => true,
            'show_watermark' => true,
            'watermark_opacity' => 0.08,
            'primary_color' => '#1a365d',
            'accent_color' => '#c9a227',
            'teacher_signature_id' => null,
            'principal_signature_id' => null,
            'signature_labels' => [
                'class_teacher' => 'Class Teacher',
                'principal' => 'Head Teacher / Principal',
                'parent' => 'Parent / Guardian',
            ],
            'rank_failed_students' => 'with_others',
            // standard: GPA card; nigerian: CA/exam columns, subject position,
            // class statistics, cumulative terms, comments and next-term block.
            'template' => 'standard',
            'show_subject_position' => true,
            'show_class_statistics' => true,
            'show_cumulative' => true,
            'show_next_term' => true,
        ];
    }

    public function mergedSettings(): array
    {
        return array_replace_recursive(self::defaults(), $this->settings ?? []);
    }
}
