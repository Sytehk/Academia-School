<?php

namespace Modules\QuestionBank\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class QuestionBankController extends Controller
{
    // public function index(Request $request)
    // {
    //     $apiUrl = 'https://api.daricomma.com/question/filter-options/00b982b7-34b5-4d53-9d22-76f881e64f69';

    //     $response = Http::withHeaders([
    //         'Authorization' => 'Bearer ' . env('BEARER_TOKEN'),
    //     ])->get($apiUrl);

    //     $apiData = $response->json();
    //     DB::transaction(function () use ($apiData) {
    //         $this->syncDataFromApi($apiData);
    //     });

    //     if ($response->successful()) {
    //         $data = $response->json();
    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Data fetched successfully',
    //             'data' => $data
    //         ]);
    //     } else {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Failed to fetch data',
    //             'error' => $response->body()
    //         ], $response->status());
    //     }
    // }

    // private function syncDataFromApi($apiData)
    // {
    //     foreach ($apiData['types'] as $type) {
    //         Type::updateOrCreate(
    //             ['id' => $type['id']],
    //             ['name' => $type['name'], 'order' => $type['order'], 'count' => $type['count']]
    //         );
    //     }

    //     foreach ($apiData['levels'] as $level) {
    //         Level::updateOrCreate(
    //             ['id' => $level['id']],
    //             [
    //                 'name' => $level['name'],
    //                 'count' => $level['count'],
    //                 'type_id' => $level['type']['id']
    //             ]
    //         );
    //     }

    //     foreach ($apiData['topics'] as $topic) {
    //         Topic::updateOrCreate(
    //             ['id' => $topic['id']],
    //             ['name' => $topic['name'], 'order' => $topic['order'], 'count' => $topic['count']]
    //         );
    //     }

    //     foreach ($apiData['sources'] as $source) {
    //         Source::updateOrCreate(
    //             ['id' => $source['id']],
    //             ['name' => $source['name'], 'order' => $source['order'], 'count' => $source['count']]
    //         );
    //     }

    //     foreach ($apiData['sub_sources'] as $subSource) {
    //         SubSource::updateOrCreate(
    //             ['id' => $subSource['id']],
    //             [
    //                 'name' => $subSource['name'],
    //                 'description' => $subSource['description'] ?? null,
    //                 'order' => $subSource['order'],
    //                 'source_id' => $subSource['source']['id'],
    //                 'count' => $subSource['count']
    //             ]
    //         );
    //     }

    //     foreach ($apiData['years'] as $year) {
    //         Year::updateOrCreate(
    //             ['id' => $year['id']],
    //             ['name' => $year['name'], 'order' => $year['order'], 'count' => $year['count']]
    //         );
    //     }

    //     foreach ($apiData['tags'] as $tag) {
    //         Tag::updateOrCreate(
    //             ['id' => $tag['id']],
    //             ['name' => $tag['name'], 'count' => $tag['count']]
    //         );
    //     }
    // }
}
