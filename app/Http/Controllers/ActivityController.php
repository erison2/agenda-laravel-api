<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Agenda API",
 *     description="API documentation for the Agenda application",
 * )
 * @OA\Tag(
 *     name="Authentication",
 *     description="Endpoints related to user authentication"
 * )
 * @OA\Tag(
 *     name="Activities",
 *     description="Endpoints related to activities"
 * )
 */

class ActivityController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/activities",
     *     summary="Get list of activities",
     *     tags={"Activities"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of activities"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Activity::query();

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date]);
        }

        return response()->json($query->get(), 200);
    }

    /**
     * @OA\Post(
     *     path="/api/activities",
     *     summary="Create a new activity",
     *     tags={"Activities"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Meeting"),
     *             @OA\Property(property="type", type="string", example="Work"),
     *             @OA\Property(property="description", type="string", example="Project meeting"),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="start_date", type="string", format="date-time", example="2023-05-01 10:00:00"),
     *             @OA\Property(property="end_date", type="string", format="date-time", example="2023-05-01 11:00:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Activity created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="type", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="start_date", type="string", format="date-time", example="2024-05-30 10:00:00"),
     *             @OA\Property(property="end_date", type="string", format="date-time", example="2024-05-30 10:00:00"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2024-05-30 10:00:00"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-05-30 10:00:00"),
     *             @OA\Property(property="id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function store(Request $request)
    {
        // Set default validation rules
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date_format:Y-m-d H:i:s|after_or_equal:today',
            'end_date' => 'required|date_format:Y-m-d H:i:s|after:start_date',
        ]);

        // Validate rules
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($this->checkIfWeekend($request->start_date, $request->end_date)) {
            return response()->json(['error' => 'Activities cannot be scheduled on weekends.'], 422);
        }

        // Validate event registered for the same date
        if ($this->checkForOverlapping($request->user_id, $request->start_date, $request->end_date)) {
            return response()->json(['error' => 'Activities cannot overlap for the same user.'], 422);
        }

        $activity = Activity::create($request->all());

        return response()->json($activity, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/activities/{id}",
     *     summary="Get activity by ID",
     *     tags={"Activities"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Activity details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Activity not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show($id)
    {
        $activity = Activity::findOrFail($id);
        return response()->json($activity, 200);
    }

    /**
     * @OA\Put(
     *     path="/api/activities/{id}",
     *     summary="Update activity",
     *     tags={"Activities"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Meeting"),
     *             @OA\Property(property="type", type="string", example="Work"),
     *             @OA\Property(property="description", type="string", example="Updated project meeting"),
     *             @OA\Property(property="start_date", type="string", format="date-time", example="2023-05-01 10:00:00"),
     *             @OA\Property(property="end_date", type="string", format="date-time", example="2023-05-01 11:00:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Activity updated"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Activity not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'start_date' => 'sometimes|required|date_format:Y-m-d H:i:s|after_or_equal:today',
            'end_date' => 'sometimes|required|date_format:Y-m-d H:i:s|after:start_date',
            'completed_date' => 'nullable|date_format:Y-m-d H:i:s',
            'status' => 'sometimes|required|string|in:open,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Converter as datas em objetos Carbon
        $start_date = Carbon::parse($activity->start_date);
        $end_date = Carbon::parse($activity->end_date);

        // Verificar se houve alteração nos campos start_date ou end_date
        $startDateChanged = $request->has('start_date') && $request->start_date !== $start_date->format('Y-m-d H:i:s');
        $endDateChanged = $request->has('end_date') && $request->end_date !== $end_date->format('Y-m-d H:i:s');

        if ($startDateChanged || $endDateChanged) {
            if ($request->has(['start_date', 'end_date'])) {
                if ($this->checkIfWeekend($request->start_date, $request->end_date)) {
                    return response()->json(['error' => 'Activities cannot be scheduled on weekends.'], 422);
                }

                if ($this->checkForOverlapping($activity->user_id, $request->start_date, $request->end_date, $id)) {
                    return response()->json(['error' => 'Activities cannot overlap for the same user.'], 422);
                }
            }
        }

        $activity->update($request->all());

        return response()->json($activity, 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/activities/{id}",
     *     summary="Delete activity",
     *     tags={"Activities"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Activity deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Activity not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function destroy($id)
    {
        $activity = Activity::findOrFail($id);
        $activity->delete();

        return response()->json(null, 204);
    }

    private function checkIfWeekend($start_date, $end_date)
    {
        $start = Carbon::parse($start_date);
        $end = Carbon::parse($end_date);

        if ($start->isWeekend() || $end->isWeekend()) {
            return true;
        }

        return false;
    }

    private function checkForOverlapping($user_id, $start_date, $end_date, $activity_id = null)
    {
        $query = Activity::where('user_id', $user_id)
            ->where(function ($query) use ($start_date, $end_date) {
                $query->whereBetween('start_date', [$start_date, $end_date])
                    ->orWhereBetween('end_date', [$start_date, $end_date])
                    ->orWhere(function ($query) use ($start_date, $end_date) {
                        $query->where('start_date', '<=', $start_date)
                            ->where('end_date', '>=', $end_date);
                    });
            });

        if ($activity_id) {
            $query->where('id', '!=', $activity_id);
        }

        return $query->exists();
    }
}
