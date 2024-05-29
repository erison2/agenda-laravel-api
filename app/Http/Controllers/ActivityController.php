<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::query();

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date]);
        }

        return response()->json($query->get(), 200);
    }

    public function store(Request $request)
    {
        // Set default validation rules
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
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

    public function show($id)
    {
        $activity = Activity::findOrFail($id);
        return response()->json($activity, 200);
    }

    public function update(Request $request, $id)
    {
        $activity = Activity::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'start_date' => 'sometimes|required|date|after_or_equal:today',
            'end_date' => 'sometimes|required|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->has(['start_date', 'end_date'])) {
            if ($this->checkIfWeekend($request->start_date, $request->end_date)) {
                return response()->json(['error' => 'Activities cannot be scheduled on weekends.'], 422);
            }

            if ($this->checkForOverlapping($request->user_id, $request->start_date, $request->end_date)) {
                return response()->json(['error' => 'Activities cannot overlap for the same user.'], 422);
            }
        }

        $activity->update($request->all());

        return response()->json($activity, 200);
    }

    public function destroy($id)
    {
        $activity = Activity::findOrFail($id);
        $activity->delete();

        return response()->json(null, 204);
    }

    private function checkIfWeekend($start_date, $end_date)
    {
        $start = strtotime($start_date);
        $end = strtotime($end_date);

        if (date('N', $start) >= 6 || date('N', $end) >= 6) {
            return true;
        }

        return false;
    }

    private function checkForOverlapping($user_id, $start_date, $end_date)
    {
        $existingActivities = Activity::where('user_id', $user_id)
            ->where(function ($query) use ($start_date, $end_date) {
                $query->whereBetween('start_date', [$start_date, $end_date])
                    ->orWhereBetween('end_date', [$start_date, $end_date])
                    ->orWhereRaw('? BETWEEN start_date AND end_date', [$start_date])
                    ->orWhereRaw('? BETWEEN start_date AND end_date', [$end_date]);
            })
            ->count();

        return $existingActivities > 0;
    }
}
