<?php

namespace App\Modules\Identity\Services;

use Illuminate\Support\Facades\Log;

class SMS
{
    public static function send($mobile, $message)
    {
        $countryCode = config('app.sms_country_code', '966');

        // Normalize mobile number: remove leading zero if present
        $mobile = ltrim($mobile, '0');

        // Validate mobile number (9 digits after removing leading zero)
        if (! preg_match('/^\d{9}$/', $mobile)) {
            Log::error('Invalid mobile number', ['mobile' => $mobile]);
            throw new \InvalidArgumentException('Invalid mobile number format');
        }

        $formattedMobile = $countryCode.$mobile;

        // Proceed with SMS logic
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->request('GET', config('app.sms_url'), [
                'query' => [
                    'user' => config('app.sms_username'),
                    'secret_key' => config('app.sms_password'),
                    'sender' => config('app.sms_sender'),
                    'to' => $formattedMobile,
                    'message' => $message,
                ],
                'timeout' => 5,
            ]);

            self::logSMSResponse($formattedMobile, $message, $response);

            return [
                'status_code' => $response->getStatusCode(),
                'mobile' => $formattedMobile,
                'response_body' => (string) $response->getBody(),
            ];
        } catch (\Exception $e) {
            self::logSMSError($formattedMobile, $message, $e);
            throw $e;
        }
    }

    private static function logSMSResponse($formattedMobile, $message, $response)
    {
        Log::info('SMS sent', [
            'mobile' => $formattedMobile,
            'message' => $message,
            'status_code' => $response->getStatusCode(),
            'response_body' => (string) $response->getBody(),
        ]);
    }

    private static function logSMSError($formattedMobile, $message, $exception)
    {
        Log::error('SMS sending failed', [
            'mobile' => $formattedMobile,
            'message' => $message,
            'error' => $exception->getMessage(),
        ]);
    }
}
