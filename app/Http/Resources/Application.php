<?php

namespace Kinko\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Application extends JsonResource
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
            'domain' => $this->domain,
            'callback_url' => $this->callback_url,
            'redirect_url' => $this->redirect_url,
            'schema' => $this->schema,
            'client_id' => $this->client_id,
        ];
    }
}
