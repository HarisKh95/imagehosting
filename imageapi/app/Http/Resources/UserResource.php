<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'age' => $this->age,
            'profile_picture' => $this->profile_picture,
            'profile_picture_url' => $this->profile_picture_url,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
            'remember_token' => $this->remember_token,
          ];
    }
}
