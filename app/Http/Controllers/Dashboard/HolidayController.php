<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\CheckAuth;
use App\Models\Dashboard\Holiday;
use App\Helpers\EnglishLanguage;
use App\Helpers\ResponseHelper;
use Http;

class HolidayController extends Controller
{
    public Holiday $holiday;
    public static function getRules()
    {
        return [
            'created_by_id' => 'required',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
            'date' => 'required|date',
            'is_local' => 'nullable|integer',
            'status' => 'required|integer',
            'token' => 'required'
        ];
    }

    public function onCreate(Request $request)
    {
        $fields = $request->validate($this->getRules());
        CheckAuth::auth($fields['token']);
        try {
            $this->holiday = new Holiday();
            $this->holiday->fill($fields);
            $this->holiday->save();
            return ResponseHelper::dataResponse('success', 201, EnglishLanguage::onCreate('Holiday', 1), null);
        } catch (\Exception $exception) {
            return ResponseHelper::dataResponse('error', 400, EnglishLanguage::onCreate('Holiday', 0), $exception->getMessage());
        }
    }

    public function onUpdateById(Request $request, $id)
    {
        $fields = $request->validate($this->getRules());
        CheckAuth::auth($fields['token']);
        try {
            $this->holiday = Holiday::findOrFail($id);
            if ($this->holiday) {
                $fields['updated_by_id'] = $fields['created_by_id'];
                $this->holiday->update($fields);
                return ResponseHelper::dataResponse('success', 200, EnglishLanguage::onUpdate('Holiday', 1), $this->holiday);
            }
            return ResponseHelper::dataResponse('error', 404, EnglishLanguage::onExist('Holiday', 3), null);
        } catch (\Exception $exception) {
            return ResponseHelper::dataResponse('error', 400, EnglishLanguage::onCreate('Holiday', 0), $exception->getMessage());
        }
    }
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
        $fields = $request->validate([
            'token' => 'required'
        ]);
        CheckAuth::auth($fields['token']);
        try {
            $holidayData = Holiday::all();
            if ($holidayData->count() !== 0) {
                $reconstructedList = [];
                foreach ($holidayData as $data) {
                    $reconstructedList[] = $this->onReconstructedList($data->id, $fields['token']);
                }
                return ResponseHelper::dataResponse('success', 200, EnglishLanguage::onGet('Holiday', 1), $reconstructedList);
            }
            return ResponseHelper::dataResponse('success', 200, EnglishLanguage::onExist('Holiday', 3), null);
        } catch (\Exception $exception) {
            return ResponseHelper::dataResponse('error', 400, EnglishLanguage::onGet('Holiday', 0), $exception->getMessage());
        }
    }

    public function onGetById(Request $request, $id)
    {

        $fields = $request->validate([
            'token' => 'required'
        ]);
        CheckAuth::auth($fields['token']);
        try {
            $holidayData = Holiday::find($id);

            if ($holidayData) {
                $holidayData = $this->onReconstructedList($holidayData->id, $fields['token']);
                return ResponseHelper::dataResponse('success', 200, EnglishLanguage::onGet('Holiday', 1), $holidayData);
            }
            return ResponseHelper::dataResponse('success', 200, EnglishLanguage::onExist('Holiday', 3), null);
        } catch (\Exception $exception) {
            return ResponseHelper::dataResponse('error', 400, EnglishLanguage::onGet('Holiday', 0), $exception->getMessage());
        }
    }

    public function onGetPaginatedList(Request $request)
    {
        $fields = $request->validate([
            'display' => 'nullable|integer',
            'page' => 'nullable|integer',
            'search' => 'nullable|string',
            'token' => 'required'
        ]);
        CheckAuth::auth($fields['token']);
        $page = $fields['page'] ?? 1;
        $display = $fields['display'] ?? 10;
        $offset = ($page - 1) * $display;
        try {
            $query = Holiday::orderBy('created_at', 'desc');
            if (isset($fields['search'])) {
                $query->where('title', 'like', '%' . $fields['search'] . '%');
            }
            $dataList = $query->limit($display)->offset($offset)->get();
            $totalPage = max(ceil($query->count() / $display), 1);
            $reconstructedList = [];
            foreach ($dataList as $key => $value) {
                $reconstructedList[] = $this->onReconstructedList($value->id, $fields['token']);
            }
            $response = [
                'total_page' => $totalPage,
                'data' => $reconstructedList,
            ];
            if ($dataList->isNotEmpty()) {
                return ResponseHelper::dataResponse('success', 200, EnglishLanguage::onPaginate('Holiday', 2), $response);
            }
            return ResponseHelper::dataResponse('error', 404, EnglishLanguage::onPaginate('Holiday', 1), null);
        } catch (\Exception $exception) {
            return ResponseHelper::dataResponse('error', 400, EnglishLanguage::onPaginate('Holiday', 0), $exception->getMessage());
        }
    }
    public function onDeleteById(Request $request, $id)
    {
        $fields = $request->validate([
            'token' => 'required'
        ]);
        CheckAuth::auth($fields['token']);
        try {
            $deletedRows = Holiday::destroy($id);
            if ($deletedRows) {
                return ResponseHelper::dataResponse('success', 200, EnglishLanguage::onDelete('Holiday', 1), null);
            }
            return ResponseHelper::dataResponse('error', 404, EnglishLanguage::onExist('Holiday', 3), null);
        } catch (\Exception $exception) {
            return ResponseHelper::dataResponse('error', 500, EnglishLanguage::onDelete('Holiday', 0), $exception->getMessage());
        }
    }

    public function onReconstructedList($id, $token)
    {
        $data = Holiday::findOrFail($id);
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
}
