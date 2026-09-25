<?php

namespace App\Http\Controllers\Api;

use App\Models\SpecialNote;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SpecialNoteResource;

class SpecialNoteController extends Controller
{
    public function index(): JsonResponse
    {
        $specialNotes = SpecialNote::all();
        return response()->json([
            'success' => true,
            'data' => SpecialNoteResource::collection($specialNotes)
        ]);
    }
}
