<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\CheckAuth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dashboard\Event;
use App\Helpers\EnglishLanguage;
use App\Helpers\ResponseHelper;
use Http;

class EventController extends Controller
{
    public Event $event;
    public static function getRules()
    {
        return [
            'created_by_id' => 'required',
            'title' => 'required|string',
            'description' => 'required|string',
            'location' => 'required|string',
            'date' => 'required|date|date_format:Y-m-d',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'is_all_day' => 'nullable|integer',
            'status' => 'required|integer',
        ];
    }

    public function onCreate(Request $request)
    {
        $fields = $request->validate($this->getRules());
        $bearerToken = $this->onGetBearerToken($request);
        CheckAuth::auth($bearerToken);
        try {
            $this->event = new Event();
            $this->event->fill($fields);
            $this->event->save();
            return ResponseHelper::dataResponse('success', 201, EnglishLanguage::onCreate('Event', 1), null);
        } catch (\Exception $exception) {
            return ResponseHelper::dataResponse('error', 400, EnglishLanguage::onCreate('Event', 0), $exception->getMessage());
        }
    }

    public function onUpdateById(Request $request, $id)
    {
        $fields = $request->validate($this->getRules());
        $bearerToken = $this->onGetBearerToken($request);
        CheckAuth::auth($bearerToken);
        try {
            $this->event = Event::findOrFail($id);
            if ($this->event) {
                $fields['updated_by_id'] = $fields['created_by_id'];
                $this->event->update($fields);
                return ResponseHelper::dataResponse('success', 200, EnglishLanguage::onUpdate('Event', 1), $this->event);
            }
            return ResponseHelper::dataResponse('error', 404, EnglishLanguage::onExist('Event', 3), null);
        } catch (\Exception $exception) {
            return ResponseHelper::dataResponse('error', 400, EnglishLanguage::onCreate('Event', 0), $exception->getMessage());
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
        $bearerToken = $this->onGetBearerToken($request);
        CheckAuth::auth($bearerToken);
        try {
            $eventData = Event::all();
            if ($eventData->count() !== 0) {
                $reconstructedList = [];
                foreach ($eventData as $data) {
                    $reconstructedList[] = $this->onReconstructedList($data->id, $bearerToken);
                }
                return ResponseHelper::dataResponse('success', 200, EnglishLanguage::onGet('Event', 1), $reconstructedList);
            }
            return ResponseHelper::dataResponse('success', 200, EnglishLanguage::onExist('Event', 3), null);
        } catch (\Exception $exception) {
            return ResponseHelper::dataResponse('error', 400, EnglishLanguage::onGet('Event', 0), $exception->getMessage());
        }
    }

    public function onGetById(Request $request, $id)
    {
        $bearerToken = $this->onGetBearerToken($request);
        CheckAuth::auth($bearerToken);
        try {
            $eventData = Event::find($id);

            if ($eventData) {
                $eventData = $this->onReconstructedList($eventData->id, $bearerToken);
                return ResponseHelper::dataResponse('success', 200, EnglishLanguage::onGet('Event', 1), $eventData);
            }
            return ResponseHelper::dataResponse('success', 200, EnglishLanguage::onExist('Event', 3), null);
        } catch (\Exception $exception) {
            return ResponseHelper::dataResponse('error', 400, EnglishLanguage::onGet('Event', 0), $exception->getMessage());
        }
    }

    public function onGetPaginatedList(Request $request)
    {
        $fields = $request->validate([
            'display' => 'nullable|integer',
            'page' => 'nullable|integer',
            'search' => 'nullable|string',
        ]);
        $bearerToken = $this->onGetBearerToken($request);
        CheckAuth::auth($bearerToken);
        $page = $fields['page'] ?? 1;
        $display = $fields['display'] ?? 10;
        $offset = ($page - 1) * $display;
        try {
            $query = Event::orderBy('created_at', 'desc');

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
                return ResponseHelper::dataResponse('success', 200, EnglishLanguage::onPaginate('Event', 2), $response);
            }
            return ResponseHelper::dataResponse('error', 404, EnglishLanguage::onPaginate('Event', 1), null);
        } catch (\Exception $exception) {
            return ResponseHelper::dataResponse('error', 400, EnglishLanguage::onPaginate('Event', 0), $exception->getMessage());
        }
    }
    public function onDeleteById(Request $request, $id)
    {
        $bearerToken = $this->onGetBearerToken($request);
        CheckAuth::auth($bearerToken);
        try {
            $deletedRows = Event::destroy($id);
            if ($deletedRows) {
                return ResponseHelper::dataResponse('success', 200, EnglishLanguage::onDelete('Event', 1), null);
            }
            return ResponseHelper::dataResponse('error', 404, EnglishLanguage::onExist('Event', 3), null);
        } catch (\Exception $exception) {
            return ResponseHelper::dataResponse('error', 500, EnglishLanguage::onDelete('Event', 0), $exception->getMessage());
        }
    }

    public function onReconstructedList($id, $token)
    {
        $data = Event::findOrFail($id);
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
