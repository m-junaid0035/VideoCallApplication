<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VideoCallController;

// Generate JWT token for a participant
Route::post('/videosdk/token', [VideoCallController::class, 'generateToken']);

// Create a new meeting (room)
Route::post('/videosdk/create-meeting', [VideoCallController::class, 'createMeeting']);

// Validate if a meeting exists
Route::get('/videosdk/validate-meeting/{roomId}', [VideoCallController::class, 'validateMeeting']);

// Fetch active participants in a meeting
Route::post('/videosdk/fetch-participants', [VideoCallController::class, 'fetchParticipants']);

// End a meeting session
Route::post('/videosdk/end-meeting', [VideoCallController::class, 'endMeeting']);
