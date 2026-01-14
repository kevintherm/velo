<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $arr =  [];

        foreach($this->collection->fields as $field) {
            if ($field->hidden) continue;
            $arr[$field->name] = $this->data[$field->name] ?? null;
            if ($request->has('expand')) $arr['expand'] = $this->data['expand'];
        }

        return $arr;
    }
}
