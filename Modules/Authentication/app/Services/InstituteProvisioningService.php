<?php

namespace Modules\Authentication\Services;

use App\Mail\InstituteOnboardingMail;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Authentication\Models\Branch;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Models\Plan;
use Modules\Authentication\Models\SAASSetting;
use Modules\Authentication\Models\Subscription;
use Modules\Authentication\Models\SubscriptionItem;
use Modules\Authentication\Models\User;
use Modules\Frontend\Models\Onboarding;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Throwable;

/**
 * Single source of truth for turning an approved onboarding request into a fully
 * provisioned tenant: institute, branch, admin user, initial settings and trial
 * subscription.
 *
 * This previously lived as two near-identical copies — one in the public
 * onboarding controller and one in the admin approval endpoint — which had
 * drifted apart in ways that silently corrupted data (see the class notes on
 * {@see createTrialSubscription()} and {@see markApproved()}). Both call sites
 * now share this implementation, so a fix lands in one place.
 *
 * Everything runs inside a single transaction. The previous public flow wrote
 * the institute, branch, user, ~39 settings rows and the subscription with no
 * transaction at all, so any mid-flow failure left an orphaned half-tenant that
 * still occupied the unique email/domain slots.
 */
class InstituteProvisioningService
{
    public function __construct(
        private InstituteDefaultSetupService $defaultSetup
    ) {}

    /**
     * Provision a tenant from an onboarding request and close the request out.
     *
     * @param  int|null  $approvedBy  Acting user, when an authenticated admin is approving.
     *                                Null on the public flow, where the approver is resolved
     *                                from configuration.
     * @return array{institute: Institute, branch: Branch, user: User, subscription: Subscription, plan: Plan}
     */
    public function provisionFromOnboarding(Onboarding $onboarding, ?int $approvedBy = null): array
    {
        return DB::transaction(function () use ($onboarding, $approvedBy) {
            $data = $onboarding->collected_data ?? [];

            $plan = $this->resolveTrialPlan();
            $institute = $this->createInstitute($onboarding, $data);
            $branch = $this->createBranch($institute);
            $user = $this->createAdminUser($institute, $branch, $onboarding, $data);

            $this->defaultSetup->apply(
                $institute,
                $branch,
                $this->resolveTemplateInstituteId(null)
            );

            $subscription = $this->createTrialSubscription($institute, $plan);

            $this->markApproved($onboarding, $approvedBy);

            return [
                'institute' => $institute,
                'branch' => $branch,
                'user' => $user,
                'subscription' => $subscription,
                'plan' => $plan,
            ];
        });
    }

    /**
     * Welcome mail carrying the tenant's credentials.
     *
     * Must be called *after* the surrounding transaction commits — mailing
     * credentials for a tenant that was rolled back is worse than not mailing.
     * It is therefore deliberately not part of {@see provisionFromOnboarding()}.
     *
     * Failure is logged and swallowed: the tenant exists by this point, so a mail
     * outage must not present the sign-up as failed.
     *
     * @param  array{institute: Institute, subscription: Subscription, plan: Plan, ...}  $result
     * @param  string  $plainPassword  Held only for the lifetime of the request; the
     *                                 stored copy is hashed.
     */
    public function sendWelcomeMail(array $result, string $plainPassword): void
    {
        $institute = $result['institute'];
        $subscription = $result['subscription'];
        $plan = $result['plan'];

        try {
            if (
                blank(config('mail.mailers.smtp.host')) ||
                blank(config('mail.mailers.smtp.username')) ||
                blank(config('mail.from.address'))
            ) {
                Log::warning('Mail configuration missing. Institute onboarding mail not sent.', [
                    'institute_id' => $institute->id,
                ]);

                return;
            }

            Mail::to($institute->email)->send(
                new InstituteOnboardingMail([
                    'institute_name' => $institute->name,
                    'institute_id' => $institute->id,
                    'email' => $institute->email,
                    'password' => $plainPassword,
                    // Read from the plan and subscription that were actually created.
                    // These were hardcoded to "Trial Package" and 14 days, so the mail
                    // contradicted the real subscription whenever the plan differed.
                    'package_name' => $plan->name,
                    'trial_days' => $subscription->start_date->diffInDays($subscription->end_date),
                    'trial_end_date' => $subscription->end_date->format('Y-m-d'),
                    'login_url' => route('login'),
                ])
            );
        } catch (Throwable $e) {
            Log::error('Institute onboarding mail failed', [
                'institute_id' => $institute->id,
                'email' => $institute->email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * The trial plan, resolved by its `is_free` flag.
     *
     * Both previous call sites hardcoded plan id 1. That silently breaks the
     * moment the plans table is reseeded or reordered, handing new sign-ups
     * whatever package happens to occupy id 1.
     *
     * @throws RuntimeException when no free plan is configured, which is a
     *                          deployment error rather than a user error.
     */
    public function resolveTrialPlan(): Plan
    {
        $plan = Plan::query()
            ->where('is_free', true)
            ->where('status', true)
            ->orderBy('id')
            ->first();

        if (! $plan) {
            throw new RuntimeException(
                'No active free trial plan is configured. Mark exactly one plan as free (is_free) to enable onboarding.'
            );
        }

        return $plan;
    }

    /**
     * A plan's duration, falling back to the configured trial length.
     *
     * Custom plans are allowed to leave `duration_days` null. Passing that
     * straight to addDays() yields an end date of "now", i.e. a subscription
     * that expires the instant it is created.
     */
    public function durationDaysFor(Plan $plan): int
    {
        $days = (int) ($plan->duration_days ?? 0);

        return $days > 0
            ? $days
            : (int) config('onboarding.trial.fallback_duration_days');
    }

    /**
     * The onboarding fields are namespaced (`institute_*`) while the institutes
     * table is not, so each one is mapped explicitly.
     *
     * Note `institute_type` and `address`: both call sites passed 'type' and
     * omitted the address entirely. Neither is fillable on the model, so the
     * submitted values were silently discarded on every single sign-up.
     *
     * @param  array<string, mixed>  $data
     */
    protected function createInstitute(Onboarding $onboarding, array $data): Institute
    {
        return Institute::create([
            'name' => $data['institute_name'],
            'institute_type' => $data['institute_type'] ?? null,
            'email' => $data['institute_email'],
            'phone' => $data['institute_phone'],
            'domain' => $data['institute_domain'] ?? null,
            'address' => $data['institute_address'] ?? null,
            'logo' => $onboarding->institute_logo,
            // Explicit, matching provisionFromAdmin(); never leave status unset.
            'status' => 1,
        ]);
    }

    protected function createBranch(Institute $institute): Branch
    {
        return Branch::create([
            'institute_id' => $institute->id,
            'name' => config('onboarding.default_branch_name'),
        ]);
    }

    /**
     * The admin user for the new tenant.
     *
     * The stored password is already a hash (see {@see Onboarding::collectedDataFrom()});
     * it is assigned as-is because the model's `hashed` cast passes an existing
     * hash through untouched. Re-hashing here — as the approval endpoint did —
     * hashes the hash and locks the owner out of their own account.
     *
     * The avatar is written to `image`, which is the actual column. Both call
     * sites wrote to a non-existent `avatar` attribute, so uploaded avatars were
     * silently dropped.
     *
     * @param  array<string, mixed>  $data
     */
    protected function createAdminUser(Institute $institute, Branch $branch, Onboarding $onboarding, array $data): User
    {
        $user = User::create([
            'institute_id' => $institute->id,
            'branch_id' => $branch->id,
            'name' => $data['user_name'],
            'email' => $data['user_email'],
            'phone' => $data['user_phone'],
            'password' => $data['password'],
            'image' => $onboarding->user_avatar,
            'user_type' => 'Admin',
        ]);

        $role = Role::query()
            ->where('name', config('onboarding.admin_role'))
            ->whereNull('institute_id')
            ->first();

        if ($role) {
            $user->assignRole($role);
            // Kept in sync for the legacy `role_id` column, which parts of the
            // UI still read directly rather than going through Spatie.
            $user->forceFill(['role_id' => $role->id])->save();
        }

        return $user;
    }

    /**
     * Provision a tenant from the SaaS admin create-school form.
     *
     * Runs inside a single transaction: institute, branch, owner, configuration
     * (defaults or a copied template school), and the trial subscription either
     * all land or none do.
     *
     * @param  array<string, mixed>  $data
     * @return array{institute: Institute, branch: Branch, user: User, subscription: Subscription, plan: Plan, setup: array}
     */
    public function provisionFromAdmin(array $data, ?int $actingUserId = null): array
    {
        return DB::transaction(function () use ($data, $actingUserId) {
            $templateId = $this->resolveTemplateInstituteId($data['template_institute_id'] ?? null);
            $plan = $this->resolveTrialPlan();

            $logoPath = $this->storeUploadedFile($data['institute_logo'] ?? null, 'institutes/');
            $avatarPath = $this->storeUploadedFile($data['user_avatar'] ?? null, 'user_avatars/');

            $institute = Institute::create([
                'name' => $data['institute_name'],
                'institute_type' => $data['institute_type'] ?? null,
                'email' => $data['institute_email'],
                'phone' => $data['institute_phone'],
                'domain' => $data['institute_domain'] ?? null,
                'address' => $data['institute_address'] ?? null,
                'logo' => $logoPath,
                'assigned_to' => $actingUserId,
                'status' => 1,
            ]);

            $branch = $this->createBranch($institute);

            $user = User::create([
                'institute_id' => $institute->id,
                'branch_id' => $branch->id,
                'name' => $data['user_name'],
                'email' => $data['user_email'],
                'phone' => $data['user_phone'],
                'password' => $data['password'],
                'image' => $avatarPath,
                'user_type' => 'Admin',
            ]);

            $role = Role::query()
                ->where('name', config('onboarding.admin_role'))
                ->whereNull('institute_id')
                ->first();

            if ($role) {
                $user->assignRole($role);
                $user->forceFill(['role_id' => $role->id])->save();
            }

            $institute->forceFill(['owner_id' => $user->id])->save();

            $setup = $this->defaultSetup->apply($institute, $branch, $templateId);
            $subscription = $this->createTrialSubscription($institute, $plan);

            return [
                'institute' => $institute->fresh(),
                'branch' => $branch,
                'user' => $user,
                'subscription' => $subscription,
                'plan' => $plan,
                'setup' => $setup,
            ];
        });
    }

    /**
     * Resolve the template school used to copy settings/grades/policies.
     *
     * An explicit id from the create form wins. Otherwise the SaaS setting
     * `default_template_institute_id` is used. Null means seed built-in defaults.
     */
    public function resolveTemplateInstituteId(mixed $requested): ?int
    {
        $id = (int) ($requested ?: SAASSetting::query()
            ->where('name', 'default_template_institute_id')
            ->value('value'));

        if ($id <= 0) {
            return null;
        }

        if (! Institute::query()->whereKey($id)->exists()) {
            throw new RuntimeException('The selected default school setup no longer exists.');
        }

        return $id;
    }

    protected function storeUploadedFile(mixed $file, string $directory): ?string
    {
        if (! $file instanceof UploadedFile) {
            return null;
        }

        return fileUploader($directory, 'png', $file);
    }

    /**
     * The trial subscription for the new institute.
     *
     * The approval endpoint hardcoded `institute_id => 1`, so approving any
     * institute created a subscription against institute 1 and left the actual
     * new tenant with none.
     *
     * `invoice_details` is assigned as an array: the model casts it to JSON, so
     * the previous json_encode() produced a double-encoded string.
     */
    protected function createTrialSubscription(Institute $institute, Plan $plan): Subscription
    {
        $startDate = Carbon::now();
        $endDate = $startDate->copy()->addDays($this->durationDaysFor($plan));

        $subscription = Subscription::create([
            'institute_id' => $institute->id,
            'plan_id' => $plan->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
            'invoice_details' => [
                'invoice_no' => 'INV-'.strtoupper(Str::random(6)),
                'issued_at' => $startDate->toDateString(),
                'notes' => $plan->name,
                'amount' => (float) $plan->price,
            ],
        ]);

        SubscriptionItem::create([
            'subscription_id' => $subscription->id,
            'reference_no' => 'PAY-'.strtoupper(Str::random(8)),
            'amount_paid' => (float) $plan->price,
            'payment_method' => 'Manual',
        ]);

        return $subscription;
    }

    /**
     * Close out the onboarding request.
     *
     * This is the fix for completed onboardings reappearing in the pending list:
     * the public flow provisioned the whole tenant but never stamped the record,
     * so it stayed `pending` with a null approved_at forever.
     *
     * Deliberately unconditional. The stamp records that provisioning *happened*;
     * it is not a policy decision, so there is no toggle to disable it. Gating it
     * on a config flag would let an operator silently reintroduce the original bug
     * — a provisioned tenant whose request sits pending forever. If a manual
     * review step is ever wanted, it belongs *before* provisioning, not here.
     */
    protected function markApproved(Onboarding $onboarding, ?int $approvedBy): void
    {
        $onboarding->forceFill([
            'status' => Onboarding::STATUS_APPROVED,
            'approved_by' => $approvedBy ?? $this->resolveApproverId(),
            'approved_at' => Carbon::now(),
        ])->save();
    }

    /**
     * Who to attribute an automatic approval to.
     *
     * Public onboarding is unauthenticated, so there is no acting user. Prefer
     * the explicitly configured super admin; otherwise fall back to the
     * lowest-id user, which on a standard install is the seeded super admin.
     * Returns null rather than guessing when no user exists — the column is
     * nullable, and a missing approver must not fail the sign-up.
     */
    protected function resolveApproverId(): ?int
    {
        $configured = config('onboarding.approver_id');

        if (! blank($configured) && User::whereKey($configured)->exists()) {
            return (int) $configured;
        }

        return User::query()->orderBy('id')->value('id');
    }
}
