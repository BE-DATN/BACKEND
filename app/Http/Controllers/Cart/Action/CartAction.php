<?php

namespace App\Http\Controllers\Cart\Action;

use App\Models\Cart_Detail;
use App\Models\Course;

class CartAction
{
    public function addCartDetail($cart, $course_id)
    {
        try {
            // dd($cart->cartDetails->where('course_id', $course_id)->first());
            // dd($course_id, $cart->cartDetails);
            if (!($course = Course::find($course_id))) {
                return [
                    'status' => false,
                    'message' => 'Không tìm thấy khóa học này nên không thể thêm vào giỏ hàng',
                ];
            }
            if ($course->status == 0) {
                return [
                    'status' => false,
                    'message' => 'Khóa học này không được Quản trị viên kích hoạt nên không thể thêm vào giỏ hàng',
                ];
            }
            // dd(2);
            if (count($cart->cartDetails) != 0) {
                foreach ($cart->cartDetails as $value) {
                    if ($value->course_id == $course_id) {
                        return ['message' => 'Sản phẩm đã được thêm vào giỏ hàng'];
                    }
                }
            }
            $cart_detail = Cart_Detail::create([
                'cart_id' => $cart->id,
                'course_id' => $course_id,
            ]);
            // dd($cart_detail);
            return ['message' => 'Sản phẩm đã được thêm vào giỏ hàng'];
            // return true;
        } catch (\Throwable $th) {
            return [
                'message' => 'Có lỗi xẩy ra khi thêm sản phẩm này vào giỏ hàng',
                'error' => $th->getMessage()
            ];
            //throw $th;
        }
    }
}
