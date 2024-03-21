<?php

namespace App\DTO\Order;

class OrderDTO
{
    public function viewOrder($order)
    {
        return [
            'id' => $order->id,
            'user' => $order->user_id,
            'total_amount' => $order->total_amount,
            'payment_method' => $order->payment_method,
            'voucher' => $order->voucher == 'null' ? 'Không':$order->voucher,
            'order_status' => $order->order_status == 0 ? 'Chờ thanh toán' : 'Đã thanh toán',
            'created_at' => date('Y-m-d H:i:s', strtotime($order->created_at)),
            'updated_at' => date('Y-m-d H:i:s', strtotime($order->updated_at)),
        ];
    }
}
