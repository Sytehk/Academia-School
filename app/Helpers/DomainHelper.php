<?php

namespace App\Helpers;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Session;

class DomainHelper
{
    /**
     * Get domain info from params or APP_URL, store in session.
     */
    public static function getDomainInfo(?string $domain = null): array
    {
        $from = 'host';

        if ($domain) {
            $from = 'params';
        } else {
            $appUrl = config('app.url');
            $parsedUrl = parse_url($appUrl, PHP_URL_HOST);
            $domain = $parsedUrl ?: $appUrl;
        }

        // Check if info already in session
        $sessionKey = 'domain_info_'.md5($domain);
        if (Session::has($sessionKey)) {
            $info = Session::get($sessionKey);
            $info['from'] = $from;

            return $info;
        }

        try {
            $client = new Client(['timeout' => 10]);
            $url = 'https://rdap.org/domain/'.urlencode($domain);
            $res = $client->get($url);
            $json = json_decode((string) $res->getBody(), true);

            $registration = null;
            $expiration = null;
            $eventsList = [];

            if (! empty($json['events']) && is_array($json['events'])) {
                foreach ($json['events'] as $ev) {
                    if (! empty($ev['eventAction']) && ! empty($ev['eventDate'])) {
                        $type = strtolower($ev['eventAction']);
                        $date = $ev['eventDate'];

                        $eventsList[] = [
                            'type' => $type,
                            'date' => $date,
                        ];

                        if (strpos($type, 'registration') !== false) {
                            $registration = $date;
                        }
                        if (strpos($type, 'expiration') !== false) {
                            $expiration = $date;
                        }
                    }
                }
            }

            $info = [
                'domain' => $domain,
                'registration' => $registration ? Carbon::parse($registration)->format('d-F-Y h:i A') : null,
                'expiration' => $expiration ? Carbon::parse($expiration)->format('d-F-Y h:i A') : null,
                'events' => $eventsList,
                'registrar' => $json['entities'][0]['vcardArray'][1][1][3] ?? $json['registrar'] ?? null,
                'status' => $json['status'] ?? [],
                'nameservers' => array_map(fn ($ns) => $ns['ldhName'] ?? null, $json['nameservers'] ?? []),
                'from' => $from,
            ];

            // Store in session
            Session::put($sessionKey, $info);

            return $info;
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'from' => $from,
            ];
        }
    }

    /**
     * Check expiration warning (within 60 days, strong alert under 30 days)
     *
     * @param  string|null  $expirationDate  ISO8601
     * @return array ['warning' => bool, 'days_left' => int, 'level' => string, 'message' => ?string]
     */
    public static function expirationWarning(?string $expirationDate): array
    {
        if (! $expirationDate) {
            return [
                'warning' => false,
                'days_left' => null,
                'level' => 'none',
                'message' => null,
            ];
        }

        $expiration = Carbon::parse($expirationDate);
        $now = Carbon::now();

        // Ensure whole day difference (no fractions)
        $daysLeft = (int) floor($now->diffInDays($expiration, false));

        // Define thresholds
        $warning = $daysLeft <= 60 && $daysLeft >= 0;
        $level = 'normal';
        $message = null;

        if ($daysLeft < 0) {
            $level = 'expired';
            $message = "❌ Your domain expired on {$expiration->format('Y-m-d')}. Please renew immediately!";
        } elseif ($daysLeft <= 7) {
            $level = 'critical';
            $message = "⚠️ Domain expiring very soon! Only {$daysLeft} days left (expires {$expiration->format('Y-m-d')}).";
        } elseif ($daysLeft <= 30) {
            $level = 'warning';
            $message = "⚠️ Please renew your domain soon. {$daysLeft} days remaining (expires {$expiration->format('Y-m-d')}).";
        } elseif ($daysLeft <= 60) {
            $level = 'notice';
            $message = "ℹ️ Your domain will expire in {$daysLeft} days on {$expiration->format('Y-m-d')}.";
        }

        return [
            'warning' => $warning,
            'days_left' => $daysLeft,
            'level' => $level,
            'message' => $message,
        ];
    }
}
