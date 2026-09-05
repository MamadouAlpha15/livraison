<?php

namespace App\Http\Resources\Api\V1;

use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $clientId = $request->user()?->id;

        return [
            'id'         => $this->id,
            'body'       => $this->body,
            'note'       => $this->note,
            'mine'       => $this->sender_id === $clientId,
            'sender'     => $this->sender?->name,
            'type'       => $this->type ?? 'text',
            'proposed_price'  => $this->proposed_price,
            'proposal_status' => $this->proposal_status,
            'images' => ($this->image_status === 'ready' && $this->images)
                ? array_map(fn ($p) => ImageOptimizer::url($p, 'medium') ?? asset('storage/'.$p), $this->images)
                : [],
            'read'       => $this->read_at !== null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
