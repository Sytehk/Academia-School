<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $name = $request->query('name');

        // If searching by language name
        if ($name) {
            $lang = Language::where('name', $name)->first();

            if (! $lang) {
                return response()->json(['error' => 'Language not found'], 404);
            }

            $filePath = storage_path("app/public/{$lang->file_path}");
            if (! file_exists($filePath)) {
                return response()->json(['error' => 'Language file not found'], 404);
            }

            $json = json_decode(file_get_contents($filePath), true);

            return response()->json([
                'name' => $lang->name,
                'file_path' => asset("storage/{$lang->file_path}"),
                'data' => $json,
            ]);
        }

        // Fetch all languages, default first
        $languages = Language::orderByDesc('is_default')->get();

        // Add full public URL to each language
        $languages->transform(function ($lang) {
            $lang->full_path = asset("storage/{$lang->file_path}");

            return $lang;
        });

        return response()->json($languages);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'file' => 'required|file|mimes:json',
        ]);

        $fileName = $request->name.'.json';
        $path = $request->file('file')->storeAs('languages', $fileName, 'public');

        $lang = Language::updateOrCreate(
            ['name' => $request->name],
            [
                'file_path' => $path,
                'is_default' => $request->input('is_default', false),
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $lang,
        ]);
    }

    public function export($name)
    {
        $lang = Language::where('name', $name)->firstOrFail();
        $filePath = storage_path("app/public/{$lang->file_path}");

        if (! file_exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return response()->download($filePath, $lang->name.'.json');
    }

    public function updateKey(Request $request, $name): JsonResponse
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'required|string',
        ]);

        $lang = Language::where('name', $name)->firstOrFail();
        $path = storage_path("app/public/{$lang->file_path}");

        if (! file_exists($path)) {
            return response()->json(['error' => 'Language file not found'], 404);
        }

        $json = json_decode(file_get_contents($path), true) ?? [];
        $json[$request->key] = $request->value;

        file_put_contents($path, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return response()->json(['success' => true, 'message' => 'Key updated']);
    }

    public function import(Request $request, $name): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:json',
        ]);

        $lang = Language::where('name', $name)->firstOrFail();
        $fileName = $name.'.json';
        $path = $request->file('file')->storeAs('languages', $fileName, 'public');

        $lang->update(['file_path' => $path]);

        return response()->json(['success' => true, 'message' => 'Language updated']);
    }

    public function setDefault($name): JsonResponse
    {
        Language::where('is_default', true)->update(['is_default' => false]);
        Language::where('name', $name)->update(['is_default' => true]);

        return response()->json(['success' => true, 'message' => "$name set as default"]);
    }
}
