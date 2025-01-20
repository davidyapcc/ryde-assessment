<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'dob' => $this->dob->format('Y-m-d'),
            'address' => $this->address,
            'description' => $this->description,
            'createdAt' => $this->created_at->toISOString(),
            'updatedAt' => $this->updated_at->toISOString(),
        ];
    }

    /**
     * Customize the outgoing response for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\Response  $response
     */
    public function withResponse($request, $response): void
    {
        $data = json_decode($response->getContent(), true);

        // If it's a collection response
        if (isset($data['data']) && is_array($data['data'])) {
            $response->setContent(json_encode([
                'status' => 'success',
                'message' => 'Users retrieved successfully',
                'data' => $data['data'],
                'meta' => $data['meta'] ?? [],
            ]));
        } else {
            // If it's a single resource response
            $response->setContent(json_encode([
                'status' => 'success',
                'message' => 'User retrieved successfully',
                'data' => $data,
            ]));
        }
    }
}
