<?php

namespace Modules\Authentication\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Academic\Services\InstituteService;
use Modules\Authentication\Http\Requests\Institute\InstituteStoreRequest;
use Modules\Authentication\Http\Requests\Institute\InstituteUpdateRequest;
use Modules\Authentication\Models\Branch;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Models\Plan;
use Modules\Authentication\Models\Subscription;
use Modules\Authentication\Models\SubscriptionItem;
use Modules\Authentication\Models\User;
use Modules\Authentication\Services\InstituteResetService;
use Modules\Frontend\Models\HomeSection;
use Modules\Frontend\Models\Theme;
use RealRashid\SweetAlert\Facades\Alert;
use Spatie\Permission\Models\Role;

class InstituteController extends Controller
{
    public function __construct(
        private InstituteService $service,
        private InstituteResetService $resetService
    ) {}

    /**
     * Whether the current user can manage every institute on the platform,
     * rather than only their own.
     */
    private function isPlatformAdmin(): bool
    {
        return auth()->check() && auth()->user()->user_type === 'SAAS Admin';
    }

    /**
     * Guard against an institute-level user reaching another tenant's institute
     * by id (route parameters are not otherwise scoped for this resource).
     */
    private function authorizeInstituteAccess(int $id): Institute|RedirectResponse
    {
        $institute = Institute::findOrFail($id);

        if (! $this->isPlatformAdmin() && (int) $institute->id !== (int) get_institute_id()) {
            Alert::error('Error', 'You are not allowed to access this institute.');

            return redirect('institutes');
        }

        return $institute;
    }

    public function index(Request $request)
    {
        $query = Institute::with([
            'owner:id,name',
            'subscription.plan:id,name',
        ])->latest();

        if (! $this->isPlatformAdmin()) {
            $query->where('id', get_institute_id());
        }

        $institutes = $query->paginate(10);

        $plans = Plan::get();

        return view('authentication::institute.index', compact('institutes', 'plans'));
    }

    public function create(): Factory|View
    {
        return view('authentication::institute.create');
    }

    public function store(InstituteStoreRequest $request): Redirector|RedirectResponse
    {
        $validated = $request->validated();

        /*
        -----------------------------------
        FILE UPLOADS
        -----------------------------------
        */
        $instituteLogoPath = null;
        if ($request->hasFile('institute_logo')) {
            $instituteLogoPath = fileUploader(
                'institutes/',
                'png',
                $request->file('institute_logo')
            );
        }

        $userAvatarPath = null;
        if ($request->hasFile('user_avatar')) {
            $userAvatarPath = fileUploader(
                'user_avatars/',
                'png',
                $request->file('user_avatar')
            );
        }

        /*
        -----------------------------------
        INSTITUTE CREATE
        -----------------------------------
        */
        $institute = Institute::create([
            'name' => $validated['institute_name'],
            'email' => $validated['institute_email'],
            'phone' => $validated['institute_phone'] ?? null,
            'institute_type' => $validated['institute_type'] ?? null,
            'domain' => $validated['institute_domain'] ?? null,
            'address' => $validated['institute_address'] ?? null,
            'logo' => $instituteLogoPath,
        ]);

        /*
        -----------------------------------
        BRANCH CREATE
        -----------------------------------
        */
        $branch = Branch::create([
            'name' => 'Main Branch',
            'institute_id' => $institute->id,
        ]);

        /*
        -----------------------------------
        USER CREATE (OWNER)
        -----------------------------------
        */
        $user = User::create([
            'institute_id' => $institute->id,
            'branch_id' => $branch->id,
            'name' => $validated['user_name'],
            'email' => $validated['user_email'],
            'phone' => $validated['user_phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'avatar' => $userAvatarPath,
            'role_id' => null,
            'user_type' => 'Admin',
        ]);

        $role = Role::where('name', 'Admin')->whereNull('institute_id')->first();
        if ($role) {
            $user->assignRole($role);
        }

        $institute->forceFill(['owner_id' => $user->id])->save();

        // Home Section
        $homeSectionsData = [
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'title' => 'About Us',
                'description' => 'Learn more about our platform, mission, and commitment to helping job seekers and employers connect successfully.',
                'serial' => 1,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'title' => 'Why Choose Us',
                'description' => 'Discover why thousands of candidates and companies trust our platform for recruitment and career growth.',
                'serial' => 2,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'title' => 'Admission',
                'description' => 'Start your journey with our easy registration and onboarding process designed for candidates and employers.',
                'serial' => 3,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'title' => 'Teachers',
                'description' => 'Meet our experienced mentors, trainers, and career experts dedicated to professional development.',
                'serial' => 4,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'title' => 'Feedback',
                'description' => 'Read success stories and testimonials from job seekers and employers using our platform.',
                'serial' => 5,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'title' => 'Mobile App',
                'description' => 'Stay connected anytime, anywhere with our mobile app for jobs, notifications, and applications.',
                'serial' => 6,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'title' => 'Events',
                'description' => 'Join career fairs, workshops, webinars, and networking events to grow your professional opportunities.',
                'serial' => 7,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'title' => 'FAQ',
                'description' => 'Find answers to the most common questions about our platform, applications, subscriptions, and services.',
                'serial' => 8,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        HomeSection::insert($homeSectionsData);

        // Default Settings
        DB::table('settings')->insert([
            // =========================
            // GENERAL SETTINGS
            // =========================
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'school_name',
                'value' => 'Demo Collage',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'site_title',
                'value' => 'Demo Title',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'tagline',
                'value' => '',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'meta_description',
                'value' => '',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'meta_keywords',
                'value' => '',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'linkedin_link',
                'value' => '',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'phone',
                'value' => '+123456789',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'email',
                'value' => 'example@gmail.com',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'language',
                'value' => 'en',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'google_map',
                'value' => '',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'address',
                'value' => '2/A NewYork, USA',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'on_google_map',
                'value' => '',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'institute_code',
                'value' => '987654',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'timezone',
                'value' => 'Asia/Dhaka',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'academic_year',
                'value' => '1',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'currency_symbol',
                'value' => '$',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'mail_type',
                'value' => 'mail',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'logo',
                'value' => 'logo.png',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'disabled_website',
                'value' => 'no',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'copyright_text',
                'value' => '&copy; Copyright 2025. All Rights Reserved by FueDevs LTD',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'exam_result_phone',
                'value' => '',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'tuition_fee_phone',
                'value' => '',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'facebook_link',
                'value' => 'https://www.facebook.com/',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'google_plus_link',
                'value' => 'https://www.google.com/',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'youtube_link',
                'value' => 'https://www.youtube.com/',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'whats_app_link',
                'value' => '',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'twitter_link',
                'value' => 'https://www.twitter.com',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'eiin_code',
                'value' => '123456',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'sms_gateway',
                'value' => 'twilio',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'bulk_sms_api_key',
                'value' => '',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'bulk_sms_sender_id',
                'value' => '',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'mimsms_username',
                'value' => '',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'mimsms_api_key',
                'value' => '',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'mimsms_sender_name',
                'value' => '',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'twilio_sid',
                'value' => '',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'twilio_token',
                'value' => '',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'twilio_from_number',
                'value' => '',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'zoom_account_id',
                'value' => '',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'zoom_client_key',
                'value' => '',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'zoom_client_secret',
                'value' => '',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'header_notice',
                'value' => '📢 Admission Open for Academic Session 2026! Students can submit applications online, track admission status, and complete enrollment through the portal. For assistance, please contact the admission office during working hours.',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'exam_result_status',
                'value' => 'no',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'admission_display_status',
                'value' => 'no',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'tc_amount',
                'value' => null,
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'primary_color',
                'value' => null,
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'secondary_color',
                'value' => null,
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'primary_container_color',
                'value' => null,
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'dark_primary_color',
                'value' => null,
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'dark_secondary_color',
                'value' => null,
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'dark_container_color',
                'value' => null,
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'text_color',
                'value' => null,
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'dark_text_colo',
                'value' => null,
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'sidebar_selected_bg_color',
                'value' => null,
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'sidebar_selected_text_color',
                'value' => null,
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'sidebar_selected_text_color',
                'value' => null,
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'guidance',
                'value' => '',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'academic_office',
                'value' => '',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'tuition_fee',
                'value' => '',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'exam_office',
                'value' => '',
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'general',
                'name' => 'website_link',
                'value' => '',
            ],
        ]);

        // Default Settings
        // =========================
        // PAYMENT CONFIG
        // =========================

        DB::table('settings')->insert([
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'payment_config',
                'mode' => 'test',
                'status' => 1,
                'name' => 'bkash',
                'payment_info' => json_encode([
                    'app_key' => '',
                    'app_secret' => '',
                    'username' => '',
                    'password' => '',
                ]),
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'payment_config',
                'mode' => 'test',
                'status' => 1,
                'name' => 'paystack',
                'payment_info' => json_encode([
                    'public_key' => '',
                    'secret_key' => '',
                    'merchant_email' => '',
                    'callback_url' => '',
                ]),
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'payment_config',
                'mode' => 'test',
                'status' => 1,
                'name' => 'razor_pay',
                'payment_info' => json_encode([
                    'api_key' => '',
                    'api_secret' => '',
                ]),
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'payment_config',
                'mode' => 'test',
                'status' => 1,
                'name' => 'stripe',
                'payment_info' => json_encode([
                    'published_key' => '',
                    'api_key' => '',
                    'mode' => 'test',
                    'other_config_key' => '',
                ]),
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'payment_config',
                'mode' => 'test',
                'status' => 1,
                'name' => 'ssl_commerz',
                'payment_info' => json_encode([
                    'store_id' => '',
                    'store_password' => '',
                ]),
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'payment_config',
                'mode' => 'test',
                'status' => 1,
                'name' => 'pvit',
                'payment_info' => json_encode([
                    'mc_tel_merchant' => '',
                    'access_token' => '',
                    'mc_merchant_code' => '',
                ]),
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'payment_config',
                'mode' => 'test',
                'status' => 1,
                'name' => 'paypal',
                'payment_info' => json_encode([
                    'client_id' => '',
                    'client_secret' => '',
                ]),
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'payment_config',
                'mode' => 'test',
                'status' => 1,
                'name' => 'paymob_accept',
                'payment_info' => json_encode([
                    'api_key' => '',
                    'integration_id' => '',
                    'iframe_id' => '',
                    'hmac_secret' => '',
                    'supported_country' => '',
                ]),
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'payment_config',
                'mode' => 'test',
                'status' => 1,
                'name' => 'flutterwave',
                'payment_info' => json_encode([
                    'public_key' => '',
                    'secret_key' => '',
                    'encryption_key' => '',
                ]),
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'payment_config',
                'mode' => 'test',
                'status' => 1,
                'name' => 'senang_pay',
                'payment_info' => json_encode([
                    'merchant_id' => '',
                    'secret_key' => '',
                ]),
            ],
            [
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'type' => 'payment_config',
                'mode' => 'test',
                'status' => 1,
                'name' => 'paytm',
                'payment_info' => json_encode([
                    'merchant_id' => '',
                    'merchant_key' => '',
                    'merchant_website_link' => '',
                    'refund_url' => '',
                ]),
            ],
        ]);

        $plan = Plan::findOrFail(1);
        $startDate = Carbon::now();
        $extraDays = $request->input('extra_days', 0);
        $endDate = $startDate->copy()->addDays($plan->duration_days + $extraDays);

        $subscription = Subscription::create([
            'institute_id' => (int) $institute->id,
            'plan_id' => $plan->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
        ]);
        SubscriptionItem::create([
            'subscription_id' => $subscription->id,
            'amount_paid' => 0,
        ]);

        // // MAIL Sent
        // try {
        //     // Check basic mail configuration
        //     if (
        //         empty(config('mail.mailers.smtp.host')) ||
        //         empty(config('mail.mailers.smtp.username')) ||
        //         empty(config('mail.from.address'))
        //     ) {
        //         Log::warning('Mail configuration missing. Institute onboarding mail not sent.', [
        //             'institute_id' => $institute->id,
        //         ]);
        //     } else {
        //         Mail::to($institute->email)->send(
        //             new InstituteOnboardingMail([
        //                 'institute_name' => $institute->name,
        //                 'institute_id' => $institute->id,
        //                 'email' => $institute->email,
        //                 'password' => $validated['password'],
        //                 'package_name' => 'Trial Package',
        //                 'trial_days' => 14,
        //                 'trial_end_date' => now()->addDays(14)->format('Y-m-d'),
        //                 'login_url' => url('/login'),
        //             ])
        //         );
        //     }
        // } catch (\Throwable $e) {
        //     // Log error but do not stop execution
        //     Log::error('Institute onboarding mail failed', [
        //         'institute_id' => $institute->id,
        //         'email' => $institute->email,
        //         'error' => $e->getMessage(),
        //     ]);
        // }

        // Return success response
        Alert::toast('Institute Create Successfully!', 'success');

        return redirect('institutes');
    }

    public function edit(int $id): Factory|View|RedirectResponse
    {
        $institute = $this->authorizeInstituteAccess($id);

        if ($institute instanceof RedirectResponse) {
            return $institute;
        }

        $institute->load('owner:id,name,email,phone');
        $themes = Theme::get();

        return view('authentication::institute.edit', compact('institute', 'themes'));
    }

    public function update(InstituteUpdateRequest $request, int $id): Redirector|RedirectResponse
    {
        $institute = $this->authorizeInstituteAccess($id);

        if ($institute instanceof RedirectResponse) {
            return $institute;
        }

        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $logoPath = $institute->logo;
            if ($request->hasFile('logo')) {
                $logoPath = fileUploader('institutes/', 'png', $request->file('logo'));
            }

            $this->service->updateInstitute([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'domain' => $validated['domain'] ?? null,
                'address' => $validated['address'] ?? null,
                'institute_type' => $validated['institute_type'] ?? null,
                'theme_id' => $validated['theme_id'] ?? null,
                'status' => $request->boolean('status'),
                'logo' => $logoPath,
            ], $id);

            if ($institute->owner) {
                $ownerData = array_filter([
                    'name' => $validated['owner_name'] ?? null,
                    'email' => $validated['owner_email'] ?? null,
                    'phone' => $validated['owner_phone'] ?? null,
                ], fn ($value) => ! blank($value));

                if (! empty($validated['owner_password'])) {
                    $ownerData['password'] = Hash::make($validated['owner_password']);
                }

                if (! empty($ownerData)) {
                    $institute->owner->update($ownerData);
                }
            }

            DB::commit();
            Alert::toast('Institute has been update successfully.', 'success');

            return redirect('institutes');
        } catch (\Exception $e) {
            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return redirect('institutes');
        }
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $institute = $this->authorizeInstituteAccess($id);

        if ($institute instanceof RedirectResponse) {
            return $institute;
        }

        DB::beginTransaction();
        try {
            $this->service->deleteInstituteById($id);

            DB::commit();
            Alert::toast('Institute has been delete successfully.', 'success');

            return redirect('institutes');
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('Error', $e->getMessage());

            return redirect('institutes');
        }
    }

    /**
     * Reset an institute's settings/theme/home content back to defaults.
     * Non-destructive: students, classes, and other operational data are untouched.
     */
    public function resetSettings(int $id): Redirector|RedirectResponse
    {
        $institute = $this->authorizeInstituteAccess($id);

        if ($institute instanceof RedirectResponse) {
            return $institute;
        }

        try {
            $this->resetService->resetSettingsToDefaults($institute);

            Alert::toast('Institute settings have been reset to defaults.', 'success');
        } catch (\Exception $e) {
            Alert::error('Error', $e->getMessage());
        }

        return redirect('institutes');
    }

    /**
     * Factory reset: wipes ALL operational data belonging to this institute
     * (students, classes, exams, invoices, etc.) and reseeds default settings.
     * This is destructive and cannot be undone.
     */
    public function factoryReset(Request $request, int $id): Redirector|RedirectResponse
    {
        $institute = $this->authorizeInstituteAccess($id);

        if ($institute instanceof RedirectResponse) {
            return $institute;
        }

        $request->validate([
            'confirm_name' => 'required|string',
        ]);

        if (trim($request->input('confirm_name')) !== $institute->name) {
            Alert::error('Error', 'The institute name you typed does not match. Factory reset cancelled.');

            return redirect('institutes');
        }

        try {
            $this->resetService->factoryReset($institute);

            Alert::toast('Institute has been factory reset. All operational data has been wiped and defaults reapplied.', 'success');
        } catch (\Exception $e) {
            Alert::error('Error', $e->getMessage());
        }

        return redirect('institutes');
    }

    public function updateSubscription(Request $request, $id)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'start_date' => 'required|date',
            'amount_paid' => 'nullable|numeric',
            'payment_method' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $id) {
            $institute = Institute::findOrFail($id);
            $plan = Plan::findOrFail($request->plan_id);

            /*
        ===============================
        Calculate Subscription Date
        ===============================
        */

            $startDate = Carbon::parse(
                $request->start_date
            );

            $endDate = $startDate->copy()
                ->addDays($plan->duration_days);

            /*
        ===============================
        Create / Update Subscription
        ===============================
        */

            $subscription = Subscription::updateOrCreate(
                [
                    'institute_id' => $institute->id,
                ],

                [
                    'plan_id' => $plan->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'active',
                    'invoice_details' => [
                        'plan' => $plan->name,
                        'price' => $plan->price,
                        'updated_by' => auth()->user()->name ?? 'Admin',
                        'date' => now()->format('Y-m-d'),
                    ],
                ]
            );

            /*
        ===============================
        Payment History
        ===============================
        */
            SubscriptionItem::create([
                'subscription_id' => $subscription->id,
                'reference_no' => 'SUB-'.time(),
                'amount_paid' => $request->amount_paid ?? 0,
                'payment_method' => $request->payment_method ?? 'manual',
            ]);
        });

        // Return success response
        Alert::toast('Subscription upgraded successfully.', 'success');

        return redirect('institutes');
    }
}
