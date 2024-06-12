<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Authentication\Models\Institute;
use Modules\SystemConfiguration\Models\Setting;

class SyncAllSettingsCommand extends Command
{
    protected $signature = 'settings:sync-all';
    // php artisan settings:sync-all

    protected $description = 'Sync all setting keys for each institute if missing';

    protected $defaultKeys = [
        'institute_name',
        'site_title',
        'phone',
        'email',
        'language',
        'google_map',
        'address',
        'on_google_map',
        'institute_code',
        'currency_symbol',
        'logo',
        'mail_type',
        'disabled_website',
        'copyright_text',
        'facebook_link',
        'google_plus_link',
        'youtube_link',
        'whats_app_link',
        'twitter_link',
        'eiin_code',
        'sms_gateway',
        'bulk_sms_api_key',
        'bulk_sms_sender_id',
        'mimsms_username',
        'mimsms_api_key',
        'mimsms_sender_name',
        'twilio_sid',
        'twilio_token',
        'twilio_from_number',
        'header_notice',
        'app_version',
        'app_url',
        'tagline',
        'favicon',
        'theme_color',
        'background_image',
        'tax_type',
        'tax_percentage',
        'service_charge',
        'default_currency',
        'billing_prefix',
        'invoice_footer',
        'enable_kitchen_print',
        'enable_customer_copy',
        'enable_online_order',
        'delivery_charge',
        'minimum_order_amount',
        'auto_accept_order',
        'estimated_preparation_time',
        'slack_webhook_url',
        'telegram_bot_token',
        'telegram_chat_id',
        'twilio_sms_enabled',
        'email_notifications',
        'whatsapp_notifications',
        'auto_backup',
        'report_timezone',
        'data_retention_days',
        'sidebar_collapsed',
        'dark_mode',
        'default_dashboard',
        'bkash',
        'paystack',
        'razor_pay',
        'stripe',
        'ssl_commerz',
        'pvit',
        'paypal',
        'paymob_accept',
        'flutterwave',
        'senang_pay',
        'paytm',
        'cash_on_delivery',
        'max_table_capacity',
        'default_shift_start',
        'default_shift_end',
        'auto_logout_idle_minutes',
        'primary_color',
        'secondary_color',
        'primary_container_color',
        'dark_primary_color',
        'dark_secondary_color',
        'dark_container_color',
        'text_color',
        'dark_text_color',
        'sidebar_selected_bg_color',
        'sidebar_selected_text_color',
        'is_online',
        'latitude',
        'longitude',
        'delivery_radius_km',
        'delivery_fee',
        'delivery_partner_count',
        'delivery_time_avg',
        'pickup_enabled',
        'opening_time',
        'closing_time',
        'auto_accept_orders',
        'pre_order_enabled',
        'max_order_capacity',
        'avg_rating',
        'review_count',
        'total_orders',
        'last_order_time',
        'last_active_time',
        'loyalty_points_enabled',
        'offers_enabled',
        'social_media_links',
        'settings',
        'uuid',
        'email_smtp_host',
        'email_smtp_port',
        'email_smtp_username',
        'email_smtp_password',
        'email_smtp_encryption',
        'twilio_api_key',
        'twilio_api_secret',
        'twilio_sender_id',
        'twilio_api_url',
        'twilio_is_default',
        'nexmo_api_key',
        'nexmo_api_secret',
        'nexmo_sender_id',
        'nexmo_api_url',
        'nexmo_is_default',
        'muthofun_api_key',
        'smsglobal_api_key',
        'smsglobal_api_secret',
        'smsglobal_sender_id',
        'smsglobal_api_url',
        'smsglobal_extra_key',
        'smsglobal_is_default',
    ];

    public function handle()
    {
        $keys = array_values(array_unique(array_merge($this->defaultKeys, [
            'school_name',
            'mimsms_username',
            'mimsms_api_key',
            'mimsms_sender_name',
            'tagline',
            'meta_description',
            'meta_keywords',
            'linkedin_link',
            'play_store_link',
            'app_store_link',
        ])));

        $added = 0;
        $institutes = Institute::with('branches')->get();

        foreach ($institutes as $institute) {
            $branches = $institute->branches;
            if ($branches->isEmpty()) {
                $this->warn("Institute ID {$institute->id} has no branches — skipped.");

                continue;
            }

            foreach ($branches as $branch) {
                foreach ($keys as $name) {
                    $setting = Setting::firstOrCreate(
                        [
                            'institute_id' => $institute->id,
                            'branch_id' => $branch->id,
                            'name' => $name,
                        ],
                        [
                            'type' => 'general',
                            'value' => '',
                        ]
                    );

                    if ($setting->wasRecentlyCreated) {
                        $added++;
                        $this->info("Added setting '{$name}' for Institute {$institute->id} / Branch {$branch->id}");
                    }
                }
            }
        }

        $this->info("Settings sync complete. Added {$added} missing keys.");

        return self::SUCCESS;
    }
}
