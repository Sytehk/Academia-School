<?php

namespace Modules\SystemConfiguration\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class SettingUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | General Settings
            |--------------------------------------------------------------------------
            */

            'school_name' => 'nullable|string|max:255',
            'theme_id' => 'nullable|string|max:255',
            'site_title' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'tuition_fee_phone' => 'nullable|string|max:50',
            'exam_result_phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'eiin_code' => 'nullable|string|max:255',
            'institute_code' => 'nullable|string|max:255',
            'tc_amount' => 'nullable|string',
            'header_notice' => 'nullable|string|max:5000',
            'admission_display_status' => 'nullable|in:yes,no',
            'exam_result_status' => 'nullable|in:yes,no',
            'app_version' => 'nullable|string|max:255',
            'app_url' => 'nullable|string|max:255',
            'play_store_link' => 'nullable|string|max:255',
            'app_store_link' => 'nullable|string|max:255',
            //
            'copyright_text' => 'nullable|string|max:500',
            'tagline' => 'nullable|string|max:500',
            'meta_description' => 'nullable|string|max:5000',
            'meta_keywords' => 'nullable|string|max:1000',
            'disabled_website' => 'nullable|in:yes,no',
            'guidance' => 'nullable|string|max:5000',
            'academic_office' => 'nullable|string|max:5000',
            'tuition_fee' => 'nullable|string|max:5000',
            'exam_office' => 'nullable|string|max:5000',
            'website_link' => 'nullable|string|max:255',

            /*
            |--------------------------------------------------------------------------
            | SEO & Analytics
            |--------------------------------------------------------------------------
            */

            'meta_title' => 'nullable|string|max:255',
            'og_image' => 'nullable|string|max:500',
            'ga4_id' => 'nullable|string|max:50',
            'gtm_id' => 'nullable|string|max:50',
            'meta_pixel_id' => 'nullable|string|max:50',

            /*
            |--------------------------------------------------------------------------
            | Control Panel
            |--------------------------------------------------------------------------
            */

            'currency_symbol' => 'nullable|string|max:20', // legacy fallback; prefer currency_id
            'currency_id' => 'nullable|integer|exists:currencies,id',
            'currency_position' => 'nullable|in:left,right',
            'timezone' => 'nullable|string|max:255',
            'institute_id' => 'nullable|integer',
            'branch_id' => 'nullable|integer',
            'academic_year' => 'nullable|integer',
            'language' => 'nullable|string|max:50',
            // Biometric device attendance: punches at/after this time (H:i) count as OUT.
            'attendance_out_after' => ['nullable', 'string', 'regex:/^([01]?\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'],

            /*
            |--------------------------------------------------------------------------
            | Email Settings
            |--------------------------------------------------------------------------
            */

            'mail_type' => 'nullable|in:mail,smtp',
            'from_email' => 'nullable|email|max:255',
            'from_name' => 'nullable|string|max:255',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|string|max:50',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|in:ssl,tls',

            /*
            |--------------------------------------------------------------------------
            | SMS Settings
            |--------------------------------------------------------------------------
            */

            'sms_type' => 'nullable|in:masking,nonmasking',
            'sender_id' => 'nullable|string|max:255',
            'api_key' => 'nullable|string|max:255',
            'sms_api' => 'nullable|string|max:1000',
            'default_sms_gateway' => 'nullable|string|max:255',
            'sms_test_mode' => 'nullable|in:true,false',

            'sms_gateway' => 'nullable|string|max:255',
            'bulk_sms_api_key' => 'nullable|string|max:255',
            'bulk_sms_sender_id' => 'nullable|string|max:255',
            'mimsms_username' => 'nullable|string|max:255',
            'mimsms_api_key' => 'nullable|string|max:255',
            'mimsms_sender_name' => 'nullable|string|max:255',
            'twilio_sid' => 'nullable|string|max:255',
            'twilio_token' => 'nullable|string|max:255',
            'twilio_from_number' => 'nullable|string|max:255',
            'infobip_base_url' => 'nullable|string|max:255',
            'infobip_api_key' => 'nullable|string|max:255',
            'infobip_sender_id' => 'nullable|string|max:255',
            'clickatell_api_key' => 'nullable|string|max:255',
            'messagebird_api_key' => 'nullable|string|max:255',
            'messagebird_originator' => 'nullable|string|max:255',

            /*
            |--------------------------------------------------------------------------
            | Social Media
            |--------------------------------------------------------------------------
            */

            'google_map' => 'nullable|string|max:10000',
            'on_google_map' => 'nullable|string|max:10000',
            'facebook_link' => 'nullable|url|max:255',
            'google_plus_link' => 'nullable|url|max:255',
            'youtube_link' => 'nullable|url|max:255',
            'whats_app_link' => 'nullable|url|max:255',
            'twitter_link' => 'nullable|url|max:255',
            'linkedin_link' => 'nullable|url|max:255',

            /*
            |--------------------------------------------------------------------------
            | Zoom Settings
            |--------------------------------------------------------------------------
            */

            'zoom_account_id' => 'nullable|string|max:255',
            'zoom_client_key' => 'nullable|string|max:255',
            'zoom_client_secret' => 'nullable|string|max:255',

            /*
            |--------------------------------------------------------------------------
            | Google Meet Settings
            |--------------------------------------------------------------------------
            */

            'google_meet_status' => 'nullable|in:1,0',
            'google_meet_client_id' => 'nullable|string|max:255',
            'google_meet_client_secret' => 'nullable|string|max:255',
            'google_meet_project_id' => 'nullable|string|max:255',
            'google_meet_redirect_uri' => 'nullable|string|max:255',
            'google_meet_api_key' => 'nullable|string|max:255',
            'google_meet_service_account_json' => 'nullable|string',
            'google_meet_calendar_id' => 'nullable|string|max:255',
            'google_meet_default_duration' => 'nullable|integer|min:1',
            'google_meet_default_timezone' => 'nullable|string|max:100',
            'google_meet_default_permission' => 'nullable|string|max:100',

            /*
            |--------------------------------------------------------------------------
            | App Color Settings
            |--------------------------------------------------------------------------
            */

            'primary_color' => 'nullable|string|max:50',
            'secondary_color' => 'nullable|string|max:50',
            'primary_container_color' => 'nullable|string|max:50',
            'dark_primary_color' => 'nullable|string|max:50',
            'dark_secondary_color' => 'nullable|string|max:50',
            'dark_container_color' => 'nullable|string|max:50',
            'text_color' => 'nullable|string|max:50',
            'dark_text_colo' => 'nullable|string|max:50',

            'sidebar_color' => 'nullable|string|max:50',
            'sidebar_text_color' => 'nullable|string|max:50',
            'sidebar_border_color' => 'nullable|string|max:50',
            'active_sidebar_background' => 'nullable|string|max:50',
            'sidebar_selected_bg_color' => 'nullable|string|max:50',
            'sidebar_selected_text_color' => 'nullable|string|max:50',
        ];
    }

    public function message(): array
    {
        return [
            //
        ];
    }
}
