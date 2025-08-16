<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;

class VideoCallController extends Controller
{
    // 1️⃣ Generate JWT token for frontend
    public function generateToken(Request $request)
    {
        $VIDEOSDK_API_KEY = env('VIDEOSDK_API_KEY');
        $VIDEOSDK_SECRET_KEY = env('VIDEOSDK_SECRET_KEY');
        $issuedAt = new \DateTimeImmutable();
        $expire = $issuedAt->modify('+2 hours')->getTimestamp();

        $roomId = $request->input('roomId', 'demo-room');
        $participantId = $request->input('participantId', 'participant-1');

        $payload = [
            'apikey' => $VIDEOSDK_API_KEY,
            'permissions' => ['allow_join', 'allow_mod'], // allow moderator actions
            'version' => 2,
            'roomId' => $roomId,
            'participantId' => $participantId,
            'roles' => ['crawler'], // rtc role for frontend join
            'iat' => $issuedAt->getTimestamp(),
            'exp' => $expire,
        ];

        $jwt = JWT::encode($payload, $VIDEOSDK_SECRET_KEY, 'HS256');

        return response()->json(['token' => $jwt]);
    }

    // 2️⃣ Create a new meeting (room)
    public function createMeeting(Request $request)
{
    $token = $request->input('token');

    $response = Http::withHeaders([
        'Authorization' => $token,
        'Content-Type' => 'application/json',
    ])->post('https://api.videosdk.live/v2/rooms', [
        'name' => $request->input('name', 'New Meeting'),
    ]);

    if ($response->successful()) {
        return response()->json([
            'roomId' => $response->json()['roomId'] ?? $response->json()['id'] ?? null,
            'message' => 'Meeting created successfully',
        ]);
    } else {
        return response()->json([
            'error' => 'Failed to create meeting',
            'details' => $response->json(),
        ], 400);
    }
}


    // 3️⃣ Validate meeting existence
    public function validateMeeting($roomId, Request $request)
    {
        $token = $request->input('token');

        $response = Http::withHeaders([
            'Authorization' => $token,
            'Content-Type' => 'application/json',
        ])->get("https://api.videosdk.live/v2/rooms/validate/{$roomId}");

        return response()->json($response->json());
    }

    // 4️⃣ Fetch active participants in a meeting
    public function fetchParticipants(Request $request)
    {
        $token = $request->input('token');
        $roomId = $request->input('roomId');

        $response = Http::withHeaders([
            'Authorization' => $token,
            'Content-Type' => 'application/json',
        ])->get("https://api.videosdk.live/v2/sessions?roomId={$roomId}");

        return response()->json($response->json());
    }

    // 5️⃣ End a meeting session
    public function endMeeting(Request $request)
    {
        $token = $request->input('token');
        $sessionId = $request->input('sessionId');

        $response = Http::withHeaders([
            'Authorization' => $token,
            'Content-Type' => 'application/json',
        ])->post("https://api.videosdk.live/v2/sessions/{$sessionId}/end");

        return response()->json($response->json());
    }
}
