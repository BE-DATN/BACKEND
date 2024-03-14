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
            dd($cart->cartDetails->where('course_id', $course_id)->first());
            Cart_Detail::create([
                'cart_id' => $cart->id,
                'course_id' => $course_id,
            ]);
            return true;
        } catch (\Throwable $th) {
            dd($th->getMessage());
            return false;
            //throw $th;
        }
    }
}
