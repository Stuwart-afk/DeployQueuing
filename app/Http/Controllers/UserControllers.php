<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\QueueTicket;

class UserControllers extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'device_id' => ['required', 'uuid'],
            'mobile_number' => ['nullable', 'string', 'max:20'],
            'platform' => 'required',
        ]);

        $today = now()->toDateString();

        $lastTicket = QueueTicket::where('queue_date', $today)
            ->orderByDesc('id')
            ->first();

        if ($lastTicket) {
            $lastNumber = (int) substr($lastTicket->tracking_number, 1);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        $trackingNumber = 'A' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $queue = QueueTicket::create([
            'name' => $validated['name'],
            'mobile_number' => $validated['mobile_number'],
            'device_id' => $validated['device_id'],
            'platform' => $validated['platform'],
            'tracking_number' => $trackingNumber,
            'status' => QueueTicket::STATUS_HOLDING,
            'access_token' => (string) Str::uuid(),
            'queue_date' => $today,
        ]);

        return redirect()->route('queue.status', ['token' => $queue->access_token]);
    }

    public function checkDevice(Request $request)
    {
        $deviceId = $request->query('device_id');
        $today = now()->toDateString();

        $ticket = QueueTicket::where('device_id', $deviceId)
            ->where('queue_date', $today)
            ->whereNotNull('access_token')
            ->whereNotIn('status', [QueueTicket::STATUS_COMPLETED])
            ->first();

        return response()->json([
            'exists' => (bool) $ticket,
            'ticket' => $ticket
        ]);
    }

    public function status($token)
    {
        $queue = QueueTicket::where('access_token', $token)->firstOrFail();

        return view('show', compact('queue'));
    }
}