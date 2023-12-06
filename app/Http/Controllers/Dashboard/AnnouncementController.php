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
    public Announcement $announcement;
    public static function getRules()
    {
        return [
            'created_by_id' => 'required',
            'cover' => 'nullable',
            'title' => 'required|string',
            'description' => 'required|string',
            'from' => 'required|string',
            'to' => 'required|string',
            'file' => 'nullable',
            'is_allow_comment' => 'required|integer',
            'is_pinned' => 'nullable|integer',
            'status' => 'required|integer',
            'type' => 'required|string',
        ];
    }

    public function onCreate(Request $request)
    {
        $fields = $request->validate($this->getRules());
        $bearerToken = $this->onGetBearerToken($request);
        CheckAuth::auth($bearerToken);
        $type = $fields['type'] == 1 ? 'Announcement' : 'Feeds';
        try {
            // $fieldsToSave = ['file', 'cover'];
            // foreach ($fieldsToSave as $field) {
            //     if (isset($fields[$field])) {
            //         $fields[$field] = $this->onUploadFunction($fields[$field]);
            //     }
            // }
            $this->announcement = new Announcement();
            $this->announcement->fill($fields);
            $this->announcement->save();
            return ResponseHelper::dataResponse('success', 201, EnglishLanguage::onCreate($type, 1), null);
        } catch (\Exception $exception) {
            return ResponseHelper::dataResponse('error', 400, EnglishLanguage::onCreate($type, 0), $exception->getMessage());
        }
    }

    public function onUpdateById(Request $request, $id)
    {
        $fields = $request->validate($this->getRules());
        $type = $fields['type'] == 1 ? 'Announcement' : 'Feeds';
        $bearerToken = $this->onGetBearerToken($request);
        CheckAuth::auth($bearerToken);
        try {
            // $fieldsToSave = ['file', 'cover'];
            // foreach ($fieldsToSave as $field) {
            //     if (isset($fields[$field])) {
            //         $fields[$field] = $this->onUploadFunction($fields[$field]);
            //     }
            // }
            $this->announcement = Announcement::findOrFail($id);
            if ($this->announcement) {
                $fields['updated_by_id'] = $fields['created_by_id'];
                $this->announcement->update($fields);
                return ResponseHelper::dataResponse('success', 200, EnglishLanguage::onUpdate($type, 1), $this->announcement);
            }
            return ResponseHelper::dataResponse('error', 404, EnglishLanguage::onExist($type, 3), null);
        } catch (\Exception $exception) {
            return ResponseHelper::dataResponse('error', 400, EnglishLanguage::onCreate($type, 0), $exception->getMessage());
        }
    }

    // public function onUploadFunction($data)
    // {
    //     $hashedName = $data->hashName();
    //     $filePath = $data->storeAs('public', $hashedName);
    //     return ENV('APP_URL') . Storage::url($filePath);
    // }

    public function getUserById($id, $token)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get(env('API_URL') . 'user/personal/' . $id)->json();

        return $response['success']['data'] ?? null;
    }

    public function getEmploymentById($id, $token)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get(env('API_URL') . 'employment-information/' . $id)->json();

        return $response['success']['data'] ?? null;
    }

    public function onGetAll(Request $request)
    {
        $bearerToken = $this->onGetBearerToken($request);
        CheckAuth::auth($bearerToken);
        try {
            $announcementData = Announcement::all();
            if ($announcementData->count() !== 0) {
                $reconstructedList = [];
                foreach ($announcementData as $data) {
                    $reconstructedList[] = $this->onReconstructedList($data->id, $bearerToken);
                }
                return ResponseHelper::dataResponse('success', 200, EnglishLanguage::onGet('Announcement', 1), $reconstructedList);
            }

        } catch (\Exception $exception) {
            return ResponseHelper::dataResponse('error', 400, EnglishLanguage::onGet('Announcement', 0), $exception->getMessage());
        }
    }

    public function onGetById(Request $request, $id)
    {
        $bearerToken = $this->onGetBearerToken($request);
        CheckAuth::auth($bearerToken);
        try {
            $announcementData = Announcement::find($id);

            if ($announcementData) {
                $announcementData = $this->onReconstructedList($announcementData->id, $bearerToken);
                return ResponseHelper::dataResponse('success', 200, EnglishLanguage::onGet('Announcement', 1), $announcementData);
            }
            return ResponseHelper::dataResponse('success', 200, EnglishLanguage::onExist('Announcement', 3), null);
        } catch (\Exception $exception) {
            return ResponseHelper::dataResponse('error', 400, EnglishLanguage::onGet('Announcement', 0), $exception->getMessage());
        }
    }

    public function onGetPaginatedList(Request $request)
    {
        $fields = $request->validate([
            'display' => 'nullable|integer',
            'page' => 'nullable|integer',
            'search' => 'nullable|string',
            'type' => 'nullable|integer',
        ]);
        $bearerToken = $this->onGetBearerToken($request);
        CheckAuth::auth($bearerToken);
        $type = $fields['type'] ?? 1;
        $page = $fields['page'] ?? 1;
        $display = $fields['display'] ?? 10;
        $offset = ($page - 1) * $display;
        $typeMessage = $type == 1 ? 'Announcement' : 'Feeds';
        try {
            $query = Announcement::where('type', $type)->orderBy('created_at', 'desc');
            if (isset($fields['search'])) {
                $query->where('title', 'like', '%' . $fields['search'] . '%');
            }
            $dataList = $query->limit($display)->offset($offset)->get();
            $totalPage = max(ceil($query->count() / $display), 1);
            $reconstructedList = [];
            foreach ($dataList as $key => $value) {
                $reconstructedList[] = $this->onReconstructedList($value->id, $bearerToken);
            }
            $response = [
                'total_page' => $totalPage,
                'data' => $reconstructedList,
            ];
            if ($dataList->isNotEmpty()) {
                return ResponseHelper::dataResponse('success', 200, EnglishLanguage::onPaginate($typeMessage, 2), $response);
            }
            return ResponseHelper::dataResponse('error', 404, EnglishLanguage::onPaginate($typeMessage, 1), null);
        } catch (\Exception $exception) {
            return ResponseHelper::dataResponse('error', 400, EnglishLanguage::onPaginate($typeMessage, 0), $exception->getMessage());
        }
    }
    public function onDeleteById(Request $request, $id)
    {
        $bearerToken = $this->onGetBearerToken($request);
        CheckAuth::auth($bearerToken);
        try {
            $deletedRows = Announcement::destroy($id);
            if ($deletedRows) {
                return ResponseHelper::dataResponse('success', 200, EnglishLanguage::onDelete('Announcement', 1), null);
            }
            return ResponseHelper::dataResponse('error', 404, EnglishLanguage::onExist('Announcement', 3), null);
        } catch (\Exception $exception) {
            return ResponseHelper::dataResponse('error', 500, EnglishLanguage::onDelete('Announcement', 0), $exception->getMessage());
        }
    }

    public function onReconstructedList($id, $token)
    {
        $data = Announcement::findOrFail($id);
        $created_by_data['personal_information'] = collect($this->getUserById($data->created_by_id, $token))->only(['first_name', 'middle_name', 'last_name']);
        $created_by_data['employment_information'] = collect($this->getEmploymentById($data->created_by_id, $token))->only(['id_picture', 'position_id']);
        $data->created_by = $created_by_data;

        if (isset($data->updated_by_id)) {
            $updated_by_data['personal_information'] = collect($this->getUserById($data->updated_by_id, $token))->only(['first_name', 'middle_name', 'last_name']);
            $updated_by_data['employment_information'] = collect($this->getEmploymentById($data->updated_by_id, $token))->only(['id_picture', 'position_id']);
            $data->updated_by = $updated_by_data;
        }
        return $data;
    }

    public function onGetBearerToken($request)
    {
        $bearerToken = $request->header('Authorization');
        $tokenWithoutBearer = str_replace('Bearer ', '', $bearerToken);
        return $tokenWithoutBearer;
    }
}

