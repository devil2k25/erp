<?php

namespace App\Http\Controllers\Api\V1\Worker;

use App\Http\Controllers\Controller;
use App\Models\Tenant\WorkerAttendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $attendance = WorkerAttendance::with(['user', 'shift'])
            ->when($request->date, fn($q, $d) => $q->whereDate('attendance_date', $d))
            ->when($request->user_id, fn($q, $u) => $q->where('user_id', $u))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest('attendance_date')
            ->paginate($request->per_page ?? 15);

        return response()->json(['success' => true, 'data' => $attendance]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id'         => 'required|uuid|exists:users,id',
            'shift_id'        => 'required|uuid|exists:shifts,id',
            'attendance_date' => 'required|date',
            'status'          => 'required|in:present,absent,half_day,late,on_leave,holiday',
            'check_in_time'   => 'nullable|date',
            'check_out_time'  => 'nullable|date',
            'notes'           => 'nullable|string',
        ]);

        $validated['marked_by'] = $request->user()->id;
        $attendance = WorkerAttendance::updateOrCreate(
            ['user_id' => $validated['user_id'], 'attendance_date' => $validated['attendance_date']],
            $validated
        );

        return response()->json(['success' => true, 'data' => $attendance->load('user', 'shift'), 'message' => 'Attendance marked'], 201);
    }

    public function show(WorkerAttendance $attendance): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $attendance->load('user', 'shift', 'marker')]);
    }

    public function update(Request $request, WorkerAttendance $attendance): JsonResponse
    {
        $attendance->update($request->only(['status', 'check_in_time', 'check_out_time', 'overtime_minutes', 'notes']));
        return response()->json(['success' => true, 'data' => $attendance->fresh()]);
    }

    public function destroy(WorkerAttendance $attendance): JsonResponse
    {
        $attendance->delete();
        return response()->json(['success' => true, 'message' => 'Attendance record deleted']);
    }

    public function bulkMark(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'records'                  => 'required|array|min:1',
            'records.*.user_id'        => 'required|uuid|exists:users,id',
            'records.*.shift_id'       => 'required|uuid|exists:shifts,id',
            'records.*.attendance_date'=> 'required|date',
            'records.*.status'         => 'required|in:present,absent,half_day,late,on_leave,holiday',
        ]);

        $created = 0;
        foreach ($validated['records'] as $record) {
            WorkerAttendance::updateOrCreate(
                ['user_id' => $record['user_id'], 'attendance_date' => $record['attendance_date']],
                array_merge($record, ['marked_by' => $request->user()->id])
            );
            $created++;
        }

        return response()->json(['success' => true, 'message' => "{$created} attendance records marked"]);
    }

    public function report(Request $request): JsonResponse
    {
        $request->validate(['from_date' => 'required|date', 'to_date' => 'required|date']);

        $report = WorkerAttendance::with('user')
            ->whereBetween('attendance_date', [$request->from_date, $request->to_date])
            ->selectRaw("user_id, status, COUNT(*) as count")
            ->groupBy('user_id', 'status')
            ->get()
            ->groupBy('user_id');

        return response()->json(['success' => true, 'data' => $report]);
    }
}
