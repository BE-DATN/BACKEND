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
        // dd($cart);
        $courses = [];
        // dd($cart->cartDetails);
        foreach ($cart->cartDetails as $cart) {
            // dd($cart->course_id);
            $data = Course::find($cart->course_id);
            // dd($data);
            if ($data) {
                array_push($courses, $courseDTO->courseDetail($data));
            }
        }
        $data = [
            'status' => true,
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

            $data = $cartAction->addCartDetail($cart, $course_id);
            // $message = [
            //     'status' => true,
            //     'message' => "Đã thêm sản phẩm vào giỏ hàng"
            // ];
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            $data['error'] = $th->getMessage();
            return response()->json($data, 500);
        }
    }



    //
    public function getCart()
    {
        $this->cart = Cart::select('*')->where('user_id', array_get($this->user, 'id'))->first();
        // dd($this->cart);
        if (!$this->cart) {
            $this->cart = Cart::create([
                'user_id' => array_get($this->user, 'id'),
            ]);
        }
        return $this->cart;
    }
    public function deleteCart($id)
    {
        try {
            $item = $this->getCart()->cartDetails->where('course_id', $id)->first();
            if ($item) {
                $item->delete();
                return response()->json(['status' => true,'message' => 'Sản phẩm đã được xóa khỏi giỏ hàng'], 200);
            } else {
                return response()->json(['status' => false,'message' => 'Sản phẩm không tồn tại trong giỏ hàng'], 200);
            };
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xẩy ra khi xóa sản phẩm này khỏi giỏ hàng',
                'error' => $th->getMessage()
            ], 200);
        }
    }
    public function remoteCart()
    {
        try {
            $cart = $this->getCart();
            DB::table('cart_details')->select('*')
                ->where('cart_id', $cart->id)->delete();
            // dd( $cart->cartDetails->where('cart_id', $cart->id));
            // $cart->cartDetails->where('cart_id', $cart->id)->delete();
            $cart->delete();
            return response()->json(['status' => true,'message' => 'Giỏ hàng đã được xóa'], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xẩy ra khi xóa sản phẩm trong giỏ hàng',
                'error' => $th->getMessage()
            ], 200);
        }
    }
}
