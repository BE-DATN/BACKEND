<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
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
            'username' => $this->username,
            'order_id' => $this->order_id,
            'voucher' => $this->voucher ? $this->voucher : null,
            'status' => $this->order_status,
            'total_amount' => $this->total_amount,
            'payment_method' => $this->payment_method,
            'created_at' => date('Y-m-d H:i', strtotime($this->created_at)),
            'checkoutUrl' => $this->checkoutUrl ? $this->checkoutUrl : null,
        ];
    }
}
