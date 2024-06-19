<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InstituteOnboardingMail extends Mailable
{
    use Queueable, SerializesModels;

    public $details;

    public function __construct(array $details)
    {
        $this->details = $details;
    }

    public function build()
    {
        return $this->subject('Welcome to '.config('app.name'))
            ->markdown('emails.onboarding')
            ->with([
                'instituteName' => $this->details['institute_name'],
                'instituteId' => $this->details['institute_id'],
                'email' => $this->details['email'],
                'password' => $this->details['password'],
                'packageName' => $this->details['package_name'],
                'trialDays' => $this->details['trial_days'],
                'trialEndDate' => $this->details['trial_end_date'],
                'loginUrl' => $this->details['login_url'],
            ]);
    }
}
