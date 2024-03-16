<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\order;
use App\Models\order_detail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function order(Request $request)
    {
        try {
            $request->input();
            $total_amount = 0;
            $user = getCurrentUser();

            $cart = Cart::select('*')
                ->where('user_id', array_get($user, 'id'))
                ->first();
            $cart_items = $cart->cartDetails;
            // dd($cart_items);
            // dd($cart);

            $payment_method = $request->input('payment') ? $request->input('payment') : 'MOMO';
            $voucher = $request->input('voucher') ? $request->input('payment') : 'null';
            $total_amount = $cart->cartDetails->sum(function ($cartDetail) {
                return $cartDetail->course->price;
            });
            DB::beginTransaction();
            $order = order::create([
                'user_id' => array_get($user, 'id'),
                'total_amount' => $total_amount,
                'payment_method' => $payment_method,
                'voucher' => $voucher,
                'order_status' => 0,
            ]);
            // dd($this->createOrderDetail($order, $cart_items));
            if ($this->createOrderDetail($order, $cart_items)) {
                DB::commit();
                $jsonResult = $this->payMomo($order);

                $this->clearCart($cart);
            } else {
                DB::rollBack();
            }
            return response()->json([
                'message' => 'Đơn hàng đã được tạo thành công.',
                'CheckOut' => array_get($jsonResult, 'payUrl')
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Có xẩy ra lỗi khi tạo đơn hàng này',
                'error' => $th->getMessage()
            ], 200);
        }
    }

    public function createOrderDetail($order, $cart_items)
    {
        try {
            $orderDetails = [];
            // dd($cart_items);
            foreach ($cart_items as $cart_item) {
                $orderDetails[] = [
                    'order_id'          => $order->id,
                    'course_id'         => $cart_item->course_id,
                    'course_name'       => $cart_item->course->name,
                    'price'             => $cart_item->course->price,
                    'joined_course'     => now(),
                    'progess_learning'  => 0,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            }
            // dd($orderDetails);
            order_detail::insert($orderDetails);
            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            // throw $th;
            return response()->json([
                'message' => 'Có xẩy ra lỗi khi tạo đơn hàng này',
                'error' => $th->getMessage()
            ], 200);
        }
        return 0;
    }

    public function clearCart($cart)
    {
        try {
            DB::table('cart_details')->select('*')
                ->where('cart_id', $cart->id)->delete();
            $cart->delete();
            return response()->json(['message' => 'Giỏ hàng đã được xóa'], 200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'message' => 'Có lỗi xẩy ra khi xóa sản phẩm trong giỏ hàng',
                'error' => $th->getMessage()
            ], 200);
        }
    }




    // momo 
    public function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            )
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        //execute post
        $result = curl_exec($ch);
        //close connection
        curl_close($ch);
        return $result;
    }


    public function payMomo($order)
    {
        $request = new Request();
        $config = '
                    {
                        "partnerCode": "MOMOBKUN20180529",
                        "accessKey": "klm05TvNBzhg7h7j",
                        "secretKey": "at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa"
                    }
                ';
        $array = json_decode($config, true);
        // dd($order);

        $endpoint = "https://test-payment.momo.vn/gw_payment/transactionProcessor";


        $partnerCode = $array["partnerCode"];
        $accessKey = $array["accessKey"];
        $secretKey = $array["secretKey"];
        $orderInfo = "Thanh toán qua MoMo";
        $amount = "$order->total_amount";
        $orderId = time() . "";
        $returnUrl = "http://course-selling.id.vn";
        $notifyurl = "http://course-selling.id.vn";
        $bankCode = "SML";
        // dd($order);
        $requestId = "Order_{$order->id}" . time() . "";
        $requestType = "payWithMoMoATM";
        $extraData = "";
    
        $rawHash = "partnerCode=".$partnerCode."&accessKey=".$accessKey."&requestId=".$requestId."&bankCode=".$bankCode."&amount=".$amount."&orderId=".$orderId."&orderInfo=".$orderInfo."&returnUrl=".$returnUrl."&notifyUrl=".$notifyurl."&extraData=".$extraData."&requestType=".$requestType;

        // dd($rawHash);
        $signature = hash_hmac("sha256", $rawHash, $secretKey);
        // dd($signature);
        $data = array(
            'partnerCode' => $partnerCode,
            // 'partnerName' => "Test",
            'partnerName' => "Dự án tốt nghiệp",
            'store_id' => "MomoTestStore",
            'accessKey' => $accessKey,
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'returnUrl' => $returnUrl,
            'bankCode' => $bankCode,
            'notifyUrl' => $notifyurl,
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        );
        $result = $this->execPostRequest($endpoint, json_encode($data));

        $jsonResult = json_decode($result, true);  // decode json
        // dd($jsonResult);
        error_log(print_r($jsonResult, true));
        // redirect()->to(array_get($jsonResult, 'payUrl'));
        return $jsonResult;
        // header('Location: ' . $jsonResult['payUrl']);
    }
    public function result(Request $request)
    {
        return response()->json(['apn'], 200);
    }
    public function apn(Request $request)
    {
        return response()->json(['apn'], 200);
    }
}
