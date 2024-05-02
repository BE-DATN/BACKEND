<?php

namespace App\Http\Controllers\Cart\Action;

use App\Models\Cart_Detail;
use App\Models\Course;
use Illuminate\Support\Facades\DB;

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
            $user = getCurrentUser();

            $purchasedCourses = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('courses', 'order_details.course_id', '=', 'courses.id')
            ->join('users', 'users.id', '=', 'courses.created_by')
            // ->where('courses.status', '1')
            ->where('orders.user_id', array_get($user, 'id'))
            ->where('orders.order_status', 1)
            ->select(
                'courses.id as course_id',
                'orders.user_id',
            )
            ->distinct()
            ->get();

            foreach ($purchasedCourses as $key => $value) {
                if ($value->course_id == $course->id) {
                    return [
                        'status' => false,
                        'message' => 'Bạn đã mua khóa học này rồi!',
                    ];
                }
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
                        return ['status' => true, 'message' => 'Sản phẩm đã được thêm vào giỏ hàng'];
                    }
                }
            }
            $cart_detail = Cart_Detail::create([
                'cart_id' => $cart->id,
                'course_id' => $course_id,
            ]);
            // dd($cart_detail);
            return ['status' => true, 'message' => 'Sản phẩm đã được thêm vào giỏ hàng'];
            // return true;
        } catch (\Throwable $th) {
            return [
                'status' => false,
                'message' => 'Có lỗi xẩy ra khi thêm sản phẩm này vào giỏ hàng',
                'error' => $th->getMessage()
            ];
            //throw $th;
        }
    }
}
