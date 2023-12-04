<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\CheckAuth;
use App\Http\Controllers\Controller;
use App\Models\Dashboard\Announcement;
use Illuminate\Http\Request;
use Http;
use App\Helpers\EnglishLanguage;
use App\Helpers\ResponseHelper;

class AnnouncementController extends Controller
{
    public function onGetAll(Request $request)
    {
        try {
            $fields = $request->validate([
                'token' => 'required'
            ]);

            // Retrieve local data
            $announcementData = Announcement::all();

            foreach ($announcementData as $data) {
                $remoteData = $this->getUserById($data->created_by_id, $fields['token']);
                \Log::info($remoteData);
            }
            // $joinedData = $localData->map(function ($localItem) use ($remoteData) {
            //     $matchingRemoteItem = collect($remoteData)->firstWhere('id', $localItem->created_by_id);

            //     $localItem->remoteItem = $matchingRemoteItem;

            //     return $localItem;
            // });

            // \Log::info($joinedData);
        } catch (\Exception $e) {
            dd($e);
            return ResponseHelper::dataResponse('error', 400, EnglishLanguage::onGet('Announcement', 0), $e->getMessage());
        }
    }


    public function getUserById($id, $token)
    {
        // Retrieve remote data
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get('http://127.0.0.1:8000/api/user/personal/' . $id)->json();

        return $response['success']['data'] ?? null;
    }
}

