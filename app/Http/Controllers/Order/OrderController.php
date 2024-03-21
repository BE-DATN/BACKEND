<?php

namespace App\Http\Controllers\Order;

use App\DTO\Order\OrderDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResouce;
use App\Models\Cart;
use App\Models\order;
use App\Models\order_detail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // Create order
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

            $payment_method = $request->input('payment') ? $request->input('payment') : 'MOMO_ATM';
            // dd($payment_method);
            $voucher = $request->input('voucher') ? $request->input('voucher') : 'null';
            $total_amount = $cart->cartDetails->sum(function ($cartDetail) {
                return $cartDetail->course->price;
            });
            // dd($total_amount);

            DB::beginTransaction();
            $order = order::create([
                'user_id' => array_get($user, 'id'),
                'total_amount' => $total_amount,
                'payment_method' => $payment_method,
                'voucher' => $voucher,
                'order_status' => 0,
                'order_id' => time() . "",
            ]);
            // dd($order);
            if ($this->createOrderDetail($order, $cart_items)) {
                DB::commit();
                switch ($payment_method) {
                    case 'MOMO_ATM':
                        $jsonResult = $this->payMomoATM($order);
                        break;
                    case 'MOMO':
                        $jsonResult = $this->payMomo($order);
                        break;

                    default:
                        $jsonResult = $this->payMomoATM($order);
                        break;
                }
                // if (array_get($jsonResult, 'payUrl')) {
                // $this->clearCart($cart);
                // }
            } else {
                DB::rollBack();
            }
            return response()->json([
                'message' => 'Đơn hàng đã được tạo thành công.',
                'order_id' => $order->id,
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

    // Create order detail
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

    // Clear cart of current user
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
    // get order with order_id
    public function viewOrder($order_id, OrderDTO $orderDTO)
    {
        try {
            $order = order::find($order_id);
            if ($order) {
                return response()->json([
                    'status' => true,
                    'order' => $orderDTO->viewOrder($order)
                ], 200);
            } {
                return response()->json([
                    'status' => false,
                    'message' => 'Không tìm thấy đơn hàng này.',
                ], 404);
            }
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => false,
                'error' => $th->getMessage()
            ], 404);
        }
    }
    // get order with order_id
    public function viewOrderDetail($order_id)
    {
        try {
            $order_detail = order_detail::where('order_id', $order_id)->get();
            if ($order_detail) {
                return response()->json([
                    'status' => true,
                    'order_detail' => OrderResouce::collection($order_detail)
                ], 200);
            } {
                return response()->json([
                    'status' => false,
                    'message' => 'Không tìm thấy các khóa học của đơn hàng này.',
                ], 404);
            }
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => false,
                'error' => $th->getMessage()
            ], 404);
        }
    }

    public function result(Request $request)
    {
        return response()->json([
            'page' => 'result',
            'data' => $request->input()
        ], 200);
    }
    public function apn(Request $request)
    {
        http_response_code(200); //200 - Everything will be 200 Oke
        if (!empty($_POST)) {
            $response = array();
            try {
                $partnerCode = $_POST["partnerCode"];
                $orderId = $_POST["orderId"];
                $requestId = $_POST["requestId"];
                $amount = $_POST["amount"];
                $orderInfo = $_POST["orderInfo"];
                $orderType = $_POST["orderType"];
                $transId = $_POST["transId"];
                $resultCode = $_POST["resultCode"];
                $message = $_POST["message"];
                $payType = $_POST["payType"];
                $responseTime = $_POST["responseTime"];
                $extraData = $_POST["extraData"];
                $m2signature = $_POST["signature"]; //MoMo signature
                $accessKey = $_POST["accessKey"];
                $errorCode = $_POST["errorCode"];


                //Checksum
                $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&message=" . $message . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo .
                    "&orderType=" . $orderType . "&partnerCode=" . $partnerCode . "&payType=" . $payType . "&requestId=" . $requestId . "&responseTime=" . $responseTime .
                    "&resultCode=" . $resultCode . "&transId=" . $transId;

                $partnerSignature = hash_hmac("sha256", $rawHash, 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa');

                if ($m2signature == $partnerSignature) {
                    if ($errorCode == '0') {
                        $result = '<div class="alert alert-success">Capture Payment Success</div>';
                    } else {
                        $result = '<div class="alert alert-danger">' . $message . '</div>';
                    }
                } else {
                    $result = '<div class="alert alert-danger">This transaction could be hacked, please check your signature and returned signature</div>';
                }
            } catch (\Exception $e) {
                echo $response['message'] = $e;
            }

            $debugger = array();
            $debugger['rawData'] = $rawHash;
            $debugger['momoSignature'] = $m2signature;
            $debugger['partnerSignature'] = $partnerSignature;

            if ($m2signature == $partnerSignature) {
                $response['message'] = "Received payment result success";
            } else {
                $response['message'] = "ERROR! Fail checksum";
            }
            $response['debugger'] = $debugger;
            echo json_encode($response);
        }
        order::where('order_id', $orderId)->first()->update(['order_status' => 100]);
        // return response()->json([
        //     'page' => 'apn',
        //     'data' => json_encode($response)
        // ], 200);
        return redirect()->away('https://google.vn')->with(json_encode($response));
    }

    // momo atm
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

    public function payMomoATM($order)
    {
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
        $returnUrl = "http://api.course-selling.id.vn/api/order/redirect-notification";
        $notifyurl = "http://api.course-selling.id.vn/api/order/payment-notification";
        $bankCode = "SML";
        // dd($order);
        $requestId = "Order_{$order->id}" . time() . "";
        $requestType = "payWithMoMoATM";
        $extraData = "";

        $rawHash = "partnerCode=" . $partnerCode . "&accessKey=" . $accessKey . "&requestId=" . $requestId . "&bankCode=" . $bankCode . "&amount=" . $amount . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&returnUrl=" . $returnUrl . "&notifyUrl=" . $notifyurl . "&extraData=" . $extraData . "&requestType=" . $requestType;

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
    }

    // Pay momo
    public function payMomo($order)
    {
        $endpoint = "https://test-payment.momo.vn/gw_payment/transactionProcessor";

        $config = '
                    {
                        "partnerCode": "MOMOBKUN20180529",
                        "accessKey": "klm05TvNBzhg7h7j",
                        "secretKey": "at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa"
                    }
                ';
        $array = json_decode($config, true);
        $partnerCode = $array["partnerCode"];
        $accessKey = $array["accessKey"];
        $secretKey = $array["secretKey"];
        $orderInfo = "Thanh toán qua MoMo";
        $amount = "$order->total_amount";
        $orderId = $order->order_id;;
        $returnUrl  = "http://api.course-selling.id.vn/api/order/redirect-notification";
        $notifyurl = "http://api.course-selling.id.vn/api/order/payment-notification";
        $extraData = "merchantName=MoMo Partner";

        $requestId = time() . "";
        $requestType = "captureMoMoWallet";

        $rawHash = "partnerCode=" . $partnerCode . "&accessKey=" . $accessKey . "&requestId=" . $requestId . "&amount=" . $amount . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&returnUrl=" . $returnUrl . "&notifyUrl=" . $notifyurl . "&extraData=" . $extraData;
        $signature = hash_hmac("sha256", $rawHash, $secretKey);
        $data = array(
            'partnerCode' => $partnerCode,
            'accessKey' => $accessKey,
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'returnUrl' => $returnUrl,
            'notifyUrl' => $notifyurl,
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        );
        $result = $this->execPostRequest($endpoint, json_encode($data));
        $jsonResult = json_decode($result, true);  // decode json
        // dd($jsonResult);
        return $jsonResult;
    }
}
