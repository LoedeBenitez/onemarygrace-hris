<?php

namespace App\Helpers;

use Http;

class CheckAuth
{
    public static function auth($token)
    {
        $response_check_token = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get(env('API_URL') . 'token/check');
        if (isset($response_check_token->json()['success'])) {
            $user_data = $response_check_token->json();

            return $user_data;
        }
        abort(ResponseHelper::dataResponse('error', 404, 'Unauthenticated', null));
    }
}
