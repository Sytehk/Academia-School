<?php

namespace Modules\Academic\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\SystemConfiguration\Models\Setting;

class GoogleMeetService
{
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';

    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const CALENDAR_BASE_URL = 'https://www.googleapis.com/calendar/v3/calendars/';

    private const SCOPE = 'https://www.googleapis.com/auth/calendar.events';

    public function getAuthorizationUrl(): string
    {
        $query = http_build_query([
            'client_id' => get_option('google_meet_client_id'),
            'redirect_uri' => get_option('google_meet_redirect_uri'),
            'response_type' => 'code',
            'scope' => self::SCOPE,
            'access_type' => 'offline',
            'prompt' => 'consent',
        ]);

        return self::AUTH_URL.'?'.$query;
    }

    public function handleOAuthCallback(string $code): bool
    {
        $response = Http::asForm()->post(self::TOKEN_URL, [
            'code' => $code,
            'client_id' => get_option('google_meet_client_id'),
            'client_secret' => get_option('google_meet_client_secret'),
            'redirect_uri' => get_option('google_meet_redirect_uri'),
            'grant_type' => 'authorization_code',
        ]);

        if (! $response->successful()) {
            return false;
        }

        $this->persistTokenResponse($response->json());

        return true;
    }

    private function persistTokenResponse(array $data): void
    {
        $this->setOption('google_meet_access_token', $data['access_token'] ?? '');

        if (! empty($data['refresh_token'])) {
            $this->setOption('google_meet_refresh_token', $data['refresh_token']);
        }

        $expiresIn = $data['expires_in'] ?? 3600;
        $this->setOption('google_meet_token_expiry', now()->addSeconds($expiresIn)->toDateTimeString());
    }

    private function setOption(string $name, $value): void
    {
        Setting::updateOrCreate(
            [
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'name' => $name,
            ],
            ['type' => 'general', 'value' => $value]
        );
    }

    private function refreshAccessToken(): ?string
    {
        $refreshToken = get_option('google_meet_refresh_token');
        if (! $refreshToken) {
            return null;
        }

        $response = Http::asForm()->post(self::TOKEN_URL, [
            'refresh_token' => $refreshToken,
            'client_id' => get_option('google_meet_client_id'),
            'client_secret' => get_option('google_meet_client_secret'),
            'grant_type' => 'refresh_token',
        ]);

        if (! $response->successful()) {
            return null;
        }

        $this->persistTokenResponse($response->json());

        return $response->json()['access_token'] ?? null;
    }

    public function getValidAccessToken(): ?string
    {
        $expiry = get_option('google_meet_token_expiry');
        $accessToken = get_option('google_meet_access_token');

        if (! $accessToken) {
            return null;
        }

        if (! $expiry || now()->greaterThanOrEqualTo($expiry)) {
            return $this->refreshAccessToken();
        }

        return $accessToken;
    }

    private function calendar()
    {
        return Http::withToken($this->getValidAccessToken());
    }

    private function calendarId(): string
    {
        return get_option('google_meet_calendar_id') ?: 'primary';
    }

    public function testConnection(): array
    {
        $token = $this->getValidAccessToken();
        if (! $token) {
            return ['success' => false, 'message' => 'Not connected. Please connect your Google account first.'];
        }

        $response = $this->calendar()->get(self::CALENDAR_BASE_URL.urlencode($this->calendarId()));

        if ($response->successful()) {
            return ['success' => true, 'message' => 'Google Calendar connection verified successfully.'];
        }

        return ['success' => false, 'message' => 'Connection failed: '.$response->body()];
    }

    /**
     * Create a Google Calendar event with a Google Meet conference attached.
     * Returns the event id and hangout link, or null on failure.
     */
    public function createMeeting(string $title, ?string $description, string $startDateTime, string $endDateTime, string $timezone): ?array
    {
        $response = $this->calendar()->post(
            self::CALENDAR_BASE_URL.urlencode($this->calendarId()).'/events?conferenceDataVersion=1',
            [
                'summary' => $title,
                'description' => $description,
                'start' => ['dateTime' => $startDateTime, 'timeZone' => $timezone],
                'end' => ['dateTime' => $endDateTime, 'timeZone' => $timezone],
                'conferenceData' => [
                    'createRequest' => [
                        'requestId' => (string) Str::uuid(),
                        'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                    ],
                ],
            ]
        );

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        return [
            'event_id' => $data['id'] ?? null,
            'meet_link' => $data['hangoutLink'] ?? null,
        ];
    }

    public function updateMeeting(string $eventId, string $title, ?string $description, string $startDateTime, string $endDateTime, string $timezone): bool
    {
        $response = $this->calendar()->patch(
            self::CALENDAR_BASE_URL.urlencode($this->calendarId()).'/events/'.urlencode($eventId),
            [
                'summary' => $title,
                'description' => $description,
                'start' => ['dateTime' => $startDateTime, 'timeZone' => $timezone],
                'end' => ['dateTime' => $endDateTime, 'timeZone' => $timezone],
            ]
        );

        return $response->successful();
    }

    public function deleteMeeting(string $eventId): bool
    {
        $response = $this->calendar()->delete(
            self::CALENDAR_BASE_URL.urlencode($this->calendarId()).'/events/'.urlencode($eventId)
        );

        return $response->successful() || $response->status() === 410;
    }
}
