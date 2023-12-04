<?php

namespace App\Helpers;

use Http;

class CheckAuth
{
    public static function auth($token)
    {
        $response_check_token = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get('127.0.0.1:8000/api/token/check');
        if (isset($response_check_token->json()['success'])) {
            $user_data = $response_check_token->json();

            return $user_data;
        }
        return ResponseHelper::dataResponse('error', 404, 'Unauthenticated', null);
    }
}
