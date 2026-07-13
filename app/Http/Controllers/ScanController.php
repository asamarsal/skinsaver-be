<?php

namespace App\Http\Controllers;

use App\Models\SkinScan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ScanController extends Controller
{
    /**
     * POST /api/scan/selfie
     */
    public function uploadSelfie(Request $request)
    {
        $request->validate([
            'selfie_image' => 'required|image|max:5120', // max 5MB
            'profile_id' => 'nullable|uuid'
        ]);

        $file = $request->file('selfie_image');
        
        // Compute hash for ZK-Privacy receipt, before we delete it
        $imageHash = hash_file('sha256', $file->getRealPath());

        // Ephemeral Storage (ZK-Privacy & Security Rules)
        // We move it to a temporary location for processing...
        $tempPath = $file->storeAs('ephemeral', Str::random(40) . '.' . $file->getClientOriginalExtension(), 'local');
        $fullPath = Storage::disk('local')->path($tempPath);

        try {
            // [Simulated AI Processing Flow]
            // Here we would send $fullPath to the OKX ASP / AI Vision Model
            // Since this is a hackathon backend, we mock the AI response based on api.md schema.
            
            $visualNotes = [
                "T-zone appears shiny",
                "Cheeks appear slightly dry",
                "Visible pores around the nose area"
            ];
            
            $faceZoneMap = [
                "t_zone" => "looks oily",
                "cheeks" => "looks slightly dry"
            ];
            
            $skinScores = [
                "oiliness" => 7,
                "texture" => 6,
                "redness" => 4,
                "hydration" => 4
            ];

            // Save scan record (WITHOUT saving the actual image)
            $scan = SkinScan::create([
                'user_id' => $request->user()->id,
                'profile_id' => $request->profile_id,
                'visual_notes' => $visualNotes,
                'skin_scores' => $skinScores,
                'image_hash' => $imageHash
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'scan_id' => $scan->id,
                    'visual_notes' => $visualNotes,
                    'possible_skin_pattern' => "combination-looking skin",
                    'face_zone_map' => $faceZoneMap,
                    'routine_priority' => ["daily sunscreen", "lightweight moisturizer"],
                    // FDA Compliance MUST HAVE:
                    'caution' => "This is not a medical diagnosis."
                ]
            ]);

        } finally {
            // CRITICAL: Auto-delete the file immediately to comply with security.md
            // "Selfie harus auto-delete setelah analisis, jangan simpan > 1 jam"
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }
}
