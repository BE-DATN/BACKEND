<?php

namespace App\Http\Controllers\Cart;

use App\Http\Resources\CourseResource;
use App\Http\Controllers\Cart\Action\CartAction;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Course;
use App\Models\Cart_Detail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\DTO\Course\CourseDTO;


use function Laravel\Prompts\select;

class CartController extends Controller
{
    /**
     * show all item in cart
     */
    protected $user = null;
    protected $cart = null;
    public function __construct()
    {
        $this->user = getCurrentUser();
    }
    public function index(CourseDTO $courseDTO)
    {
        $cart = $this->getCart();
        // $items = $cart->cartDetails;
        // dd();
        $courses = [];
        foreach ($cart->cartDetails as $cart) {
            $data = Course::find($cart->course_id);
            array_push($courses, $courseDTO->courseDetail($data));
        }
        $data = [
            'status' => "ok",
            'courses' => $courses,
            // 'item' => $items
        ];
        return response()->json($data, 200);
    }
    public function addCart($course_id, CartAction $cartAction)
    {
        try {
            array_get($this->user, 'id');
            $cart = $this->getCart();
            $cartAction->addCartDetail($cart, $course_id);
            $message = [
                'status' => true,
                'message' => "Đã thêm sản phẩm vào giỏ hàng"
            ];
            return response()->json($message, 200);
        } catch (\Throwable $th) {
            $message = [
                'status' => false,
                'message' => "Không thể thêm sản phẩm này vào giỏ hàng",
                'error' => $th->getMessage()
            ];
            return response()->json($message, 500);
        }
    }



    //
    public function getCart() {
        $this->cart = Cart::select('*')->where('user_id', array_get($this->user, 'id'))->first();
        // dd($this->cart);
        if (!$this->cart) {
            $this->cart = Cart::create([
                'user_id' => array_get($this->user, 'id'),
            ]);
        }
        return $this->cart;
    }
    public function deleteCart($id) {
        try {
            if ($this->getCart()->cartDetails->where('course_id', $id)->first()->delete()) {
                return response()->json(['message' => 'Sản phẩm đã được xóa khỏi giỏ hàng'], 200);
            };
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'message' => 'Có lỗi xẩy ra khi xóa sản phẩm này khỏi giỏ hàng',
                'error' =>$th->getMessage()
            ], 200);
        }
    }
    public function remoteCart() {
        try {
            $cart = $this->getCart();
            DB::table('cart_details')->select('*')
            ->where('cart_id', $cart->id)->delete();
            // dd( $cart->cartDetails->where('cart_id', $cart->id));
            // $cart->cartDetails->where('cart_id', $cart->id)->delete();
            $cart->delete();
            return response()->json(['message' => 'Giỏ hàng đã được xóa'], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'message' => 'Có lỗi xẩy ra khi xóa sản phẩm trong giỏ hàng',
                'error' =>$th->getMessage()
            ], 200);
        }
    }
}
