<?php

namespace App\Http\Controllers\Cart\Action;

use App\Models\Cart_Detail;

class CartAction
{
    // protected $request;
    // public function __construct(Request $request)
    // {
    //     $this->request = $request;
    // }

    public function addCartDetail($cart, $course_id)
    {
        try {
            // dd($cart->cartDetails->where('course_id', $course_id)->first());
            if (count($cart->cartDetails) != 0) {
                foreach ($cart->cartDetails as $value) {
                    if ($value->course_id == $course_id) {
                        return response()->json(['message' => 'Sản phẩm đã được thêm vào giỏ hàng'], 200);
                    }
                }
            }
            Cart_Detail::create([
                'cart_id' => $cart->id,
                'course_id' => $course_id,
            ]);
            return response()->json(['message' => 'Sản phẩm đã được thêm vào giỏ hàng'], 200);
            // return true;
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Có lỗi xẩy ra khi thêm sản phẩm này vào giỏ hàng',
                'error' =>$th->getMessage()
            ], 200);
            //throw $th;
        }
    }
}
