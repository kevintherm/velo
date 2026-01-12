<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecordRequest;
use App\Http\Resources\RecordResource;
use App\Models\Collection;
use App\Models\Record;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class RecordController extends Controller
{
    public function index(Request $request, Collection $collection): JsonResponse
    {
        $perPage = $request->input('perPage', 100);
        $records = $collection->records()->simplePaginate($perPage);
        return RecordResource::collection($records)->response();
    }

    public function show(Collection $collection, string $recordId): JsonResponse
    {
        $record = $collection->queryCompiler()->filter('id', '=', $recordId)->firstOrFail();
        $resource = new RecordResource($record);
        return $resource->response();
    }

    public function store(RecordRequest $request, Collection $collection)
    {
        $record = $collection->records()->create(['data' => $request->validated()]);
        $resource = new RecordResource($record);
        return $resource->response();
    }

    public function update(RecordRequest $request, Collection $collection, string $recordId)
    {
        $record = $collection->queryCompiler()->filter('id', '=', $recordId)->firstRawOrFail();

        $record->update([
            'data' => [...$record->data->toArray(), ...$request->validated()]
        ]);

        $resource = new RecordResource($record);
        return $resource->response();
    }

    public function delete(Collection $collection, string $recordId)
    {
        $record = $collection->queryCompiler()->filter('id', '=', $recordId)->firstRawOrFail();
        $record->delete();

        return Response::noContent();
    }
}
