<?php

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\Cart\Action\CartAction;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Cart_Detail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    public function index()
    {
        $this->getCart();
        $items = 0;
        $data = [
            'status' => "ok",
            'data' => $items
        ];
        return response()->json($data, 200);
    }
    public function addCart($course_id, CartAction $cartAction)
    {
        try {
            array_get($this->user, 'id');
            $cart = $this->getCart();  
            // dd($course_id);  
            $cartAction->addCartDetail($cart, $course_id);
            $message = [
                'status' => true,
                'message' => "Đã thêm sản phẩm vào giỏ hàng"
            ];
            return response()->json($message, 200);
        } catch (\Throwable $th) {
            //throw $th;
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
}
