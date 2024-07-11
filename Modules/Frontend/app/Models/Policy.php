<?php

namespace Modules\Frontend\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $table = 'policies';

    public const TABLE_NAME = 'policies';

    public const TYPE_PRIVACY = 1;

    public const TYPE_COOKIE = 2;

    public const TYPE_TERMS = 3;

    public const TYPE_REFUND = 4;

    public const TYPE_LEGAL = 5;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'type',
        'description',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];

    public static function titles(): array
    {
        return [
            self::TYPE_PRIVACY => 'Privacy Policy',
            self::TYPE_COOKIE => 'Cookie Policy',
            self::TYPE_TERMS => 'Terms & Conditions',
            self::TYPE_REFUND => 'Refund Policy',
            self::TYPE_LEGAL => 'Legal',
        ];
    }

    public static function titleFor(int $type): string
    {
        return self::titles()[$type] ?? 'Policy';
    }

    public static function defaultDescriptions(): array
    {
        return [
            self::TYPE_PRIVACY => 'This Privacy Policy explains how we collect, use, and protect your personal data when you use our school platform. We prioritize your privacy and comply with applicable data protection laws.',
            self::TYPE_COOKIE => 'Our Cookie Policy explains how we use cookies to improve your experience on our platform. By using our site, you agree to our use of cookies for analytics, personalization, and security.',
            self::TYPE_TERMS => 'These Terms & Conditions outline the rules and regulations for using our school platform. By accessing our services, you agree to abide by these terms, including content usage, user conduct, and dispute resolution.',
            self::TYPE_REFUND => 'This Refund Policy explains when tuition, admission, or other school fees may be refunded, how to submit a refund request, and the timelines that apply. Please contact the school office for case-specific guidance.',
            self::TYPE_LEGAL => 'This Legal page outlines the legal notices, disclaimers, and institutional information for our school website.',
        ];
    }

    /**
     * Create any missing default policy pages for a tenant. Existing rows are left
     * untouched so school admins can only edit the assigned pages.
     */
    public static function ensureDefaultsFor(int $instituteId, int $branchId): void
    {
        $now = now();

        foreach (self::defaultDescriptions() as $type => $description) {
            $exists = static::query()
                ->where('institute_id', $instituteId)
                ->where('type', $type)
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                continue;
            }

            static::query()->create([
                'institute_id' => $instituteId,
                'branch_id' => $branchId,
                'type' => $type,
                'description' => $description,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
