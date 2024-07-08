<?php

namespace Modules\Authentication\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Authentication\Models\Institute;

class SAASSettingUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'site_name' => 'nullable|string|max:255',
            'site_title' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'office_phone' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:255',
            'google_map' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'on_google_map' => 'nullable|string|max:255',
            'currency_symbol' => 'nullable|string|max:255',
            'logo' => 'nullable|string|max:255',
            'disabled_website' => 'nullable|string|max:255',
            'copyright_text' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:1000',
            'meta_keywords' => 'nullable|string|max:500',
            'og_image' => 'nullable|string|max:500',
            'ga4_id' => 'nullable|string|max:50',
            'gtm_id' => 'nullable|string|max:50',
            'meta_pixel_id' => 'nullable|string|max:50',
            'facebook_link' => 'nullable|string|max:255',
            'google_plus_link' => 'nullable|string|max:255',
            'youtube_link' => 'nullable|string|max:255',
            'whats_app_link' => 'nullable|string|max:255',
            'twitter_link' => 'nullable|string|max:255',
            'header_notice' => 'nullable|string|max:255',
            'app_version' => 'nullable|string|max:255',
            'app_url' => 'nullable|string|max:255',
            'play_store_link' => 'nullable|string|max:255',
            'app_store_link' => 'nullable|string|max:255',
            'primary_color' => 'nullable|string|max:255',
            'secondary_color' => 'nullable|string|max:255',
            'primary_container_color' => 'nullable|string|max:255',
            'dark_primary_color' => 'nullable|string|max:255',
            'dark_secondary_color' => 'nullable|string|max:255',
            'dark_container_color' => 'nullable|string|max:255',
            'text_color' => 'nullable|string|max:255',
            // The seeded row is misspelled `dark_text_colo`; accept both so the
            // existing row can be written and the correct one kept in step.
            'dark_text_colo' => 'nullable|string|max:255',
            'dark_text_color' => 'nullable|string|max:255',
            'sidebar_selected_bg_color' => 'nullable|string|max:255',
            'sidebar_selected_text_color' => 'nullable|string|max:255',
            'bkash' => 'nullable|string',
            'paystack' => 'nullable|string',
            'razor_pay' => 'nullable|string',
            'stripe' => 'nullable|string',
            'ssl_commerz' => 'nullable|string',
            'pvit' => 'nullable|string',
            'paypal' => 'nullable|string',
            'paymob_accept' => 'nullable|string',
            'flutterwave' => 'nullable|string',
            'senang_pay' => 'nullable|string',
            'paytm' => 'nullable|string',
            'vercel_project_id' => 'nullable|string|max:255',
            'vercel_token' => 'nullable|string|max:255',
            'backup_destination' => 'nullable|in:local,s3',
            'backup_auto_enabled' => 'nullable|in:yes,no',
            'backup_retention_days' => 'nullable|integer|min:1|max:365',
            'backup_aws_key' => 'nullable|string|max:255',
            'backup_aws_secret' => 'nullable|string|max:255',
            'backup_aws_region' => 'nullable|string|max:100',
            'backup_aws_bucket' => 'nullable|string|max:255',
            'backup_aws_endpoint' => 'nullable|string|max:255',
            'backup_aws_use_path_style' => 'nullable|in:yes,no',
            'default_template_institute_id' => [
                'nullable',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === '' || $value === null) {
                        return;
                    }

                    if (! Institute::query()->whereKey((int) $value)->exists()) {
                        $fail('The selected default school setup is invalid.');
                    }
                },
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->exists('default_template_institute_id')) {
            return;
        }

        $templateId = $this->input('default_template_institute_id');

        if ($templateId === '' || $templateId === null) {
            $this->merge(['default_template_institute_id' => '']);

            return;
        }

        $this->merge(['default_template_institute_id' => (string) (int) $templateId]);
    }

    public function message(): array
    {
        return [
            //
        ];
    }
}
