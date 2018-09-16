<?php

namespace Kinko\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Client extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'homepage_url' => $this->homepage_url,
            'logo_url' => $this->when(
                !is_null($this->logo_url),
                $this->logo_url
            ),
            'validated' => $this->validated,
            'revoked' => $this->revoked,
            'schema' => $this->schema,
        ];
    }
}
