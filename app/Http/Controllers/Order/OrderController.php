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

            $payment_method = $request->input('payment') ? $request->input('payment') : 'MOMO';
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
                'order_id' => "ODR-" . time() . "",
            ]);
            // dd($order);
            if ($this->createOrderDetail($order, $cart_items)) {
                // DB::commit();
                switch ($payment_method) {
                    case 'MOMO_ATM':
                        $jsonResult = $this->payMomoATM($order);
                        break;
                    case 'MOMO':
                        $jsonResult = $this->payMomo($order);
                        break;
                    case 'VNPAY':
                        $jsonResult = $this->vnpay($order);
                        break;
                    default:
                        $jsonResult = $this->payMomo($order);
                        break;
                }
                // nếu không trả về link thanh toán
                if (array_get($jsonResult, 'payUrl')) {
                    DB::commit();
                    order::find($order->id)->update(['checkoutUrl' => array_get($jsonResult, 'payUrl')]);
                    $this->clearCart($cart);
                } else {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'Có lỗi xẩy ra từ nhà cung cấp dịch vụ thanh toán.',
                    ], 400);
                }
                //
            } else {
                DB::rollBack();
            }
            return response()->json([
                'status' => true,
                'message' => 'Đơn hàng đã được tạo thành công.',
                'order_id' => $order->id,
                'CheckOut' => array_get($jsonResult, 'payUrl')
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Có xẩy ra lỗi khi tạo đơn hàng này',
                'error' => $th->getMessage()
            ], 400);
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
        http_response_code(200); //200 - Everything will be 200 Oke
        $array = $this->config();
        try {
            $accessKey = $array["accessKey"];
            $secretKey = $array["secretKey"];

            $partnerCode = $request->input(["partnerCode"]);
            $orderId = $request->input(["orderId"]);
            $requestId = $request->input(["requestId"]);
            $amount = $request->input(["amount"]);
            $orderInfo = $request->input(["orderInfo"]);
            $orderType = $request->input(["orderType"]);
            $transId = $request->input(["transId"]);
            $resultCode = $request->input(["resultCode"]);
            $message = $request->input(["message"]);
            $payType = $request->input(["payType"]);
            $responseTime = $request->input(["responseTime"]);
            $extraData = $request->input(["extraData"]);
            $m2signature = $request->input(["signature"]); //MoMo signature

            //Checksum
            $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&message=" . $message . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&orderType=" . $orderType . "&partnerCode=" . $partnerCode . "&payType=" . $payType . "&requestId=" . $requestId . "&responseTime=" . $responseTime . "&resultCode=" . $resultCode . "&transId=" . $transId;
            $partnerSignature = hash_hmac("sha256", $rawHash, $secretKey);
            // dd($request->input(), ['new' => $partnerSignature]);

            if ($m2signature == $partnerSignature) {
                // dd('=');
                if ($resultCode == '0') {
                    $data = [
                        'Payment_status' => $message,
                        'order_id' => $orderId,
                        'amount' => $amount,
                        'Payment_method' => $payType
                    ];
                    order::where('order_id', $orderId)->first()->update(['order_status' => 1, 'checkoutUrl' => 'done']);
                    // dd(order::where('order_id', $orderId)->first());
                    DB::table('logs')->insert(['log' => json_encode($data)]);
                } else {
                    $data = [
                        'Payment_status' => $message,
                        'order_id' => $orderId,
                    ];
                    DB::table('logs')->insert(['log' => json_encode($data)]);
                }
            } else {
                // dd('!=');
                $data = [
                    'danger' => "Giao dịch này có thể bị hack, vui lòng kiểm tra chữ ký của bạn và trả lại chữ ký",
                    'order_id' => $orderId,
                ];
                DB::table('logs')->insert(['log' => json_encode($data)]);
            }
        } catch (\Exception $e) {
            DB::table('logs')->insert(['log' => $e->getMessage()]);
        }

        $debugger = array();

        if ($m2signature == $partnerSignature) {
            $debugger['rawData'] = $rawHash;
            $debugger['momoSignature'] = $m2signature;
            $debugger['partnerSignature'] = $partnerSignature;
            $debugger['message'] = "Received payment result success";
        } else {
            $debugger['rawData'] = $rawHash;
            $debugger['momoSignature'] = $m2signature;
            $debugger['partnerSignature'] = $partnerSignature;
            $debugger['message'] = "ERROR! Fail checksum";
        }
        return view('success', [
            'response' => $request->input(),
            'debugger' => $debugger
        ]);
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
        $array = $this->config();
        $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";

        $partnerCode = $array["partnerCode"];
        $accessKey = $array["accessKey"];
        $serectkey = $array["secretKey"];

        $orderInfo = "Thanh toán qua MoMo";
        $amount = "$order->total_amount";
        $orderId = $order->order_id;
        $redirectUrl = "http://api.course-selling.id.vn/api/order/redirect-notification";
        $ipnUrl = "http://api.course-selling.id.vn/api/order/payment-notification";
        $bankCode = "SML";
        $extraData = "";

        // $requestId = "Order_{$order->id}" . time() . "";
        // $requestType = "payWithMoMoATM";
        $requestId = time() . "";
        $requestType = "payWithATM";

        $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
        $signature = hash_hmac("sha256", $rawHash, $serectkey);

        // dd($signature);
        $data = array(
            'partnerCode' => $partnerCode,
            'partnerName' => "Test",
            "storeId" => "MomoTestStore",
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        );
        $result = $this->execPostRequest($endpoint, json_encode($data));
        $jsonResult = json_decode($result, true);  // decode json
        error_log(print_r($jsonResult, true));
        // dd($jsonResult);
        return $jsonResult;
    }

    // Pay momo
    public function payMomo($order)
    {
        $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
        $array = $this->config();

        $partnerCode = $array["partnerCode"];
        $accessKey = $array["accessKey"];
        $secretKey = $array["secretKey"];
        $orderInfo = "Thanh toán qua MoMo";
        $amount = "$order->total_amount";
        $orderId = $order->order_id;;
        // $extraData = "merchantName=MoMo Partner";
        $extraData = "";

        $requestId = time() . "";
        $requestType = "captureWallet";
        $redirectUrl =  "http://api.course-selling.id.vn/api/order/redirect-notification";
        $ipnUrl = "http://api.course-selling.id.vn/api/order/payment-notification";
        $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
        $signature = hash_hmac("sha256", $rawHash, $secretKey);
        // dd($signature);
        $data = array(
            'partnerCode' => $partnerCode,
            // 'partnerName' => "Test",
            // "storeId" => "MomoTestStore",
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        );
        $result = $this->execPostRequest($endpoint, json_encode($data));
        $jsonResult = json_decode($result, true);  // decode json
        // dd($jsonResult);
        return $jsonResult;
    }
    // public function payMomo2($order)
    // {
    //     // $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
    //     $endpoint = "https://test-payment.momo.vn/gw_payment/transactionProcessor";

    //     $array = $this->config();

    //     $partnerCode = $array["partnerCode"];
    //     $accessKey = $array["accessKey"];
    //     $secretKey = $array["secretKey"];
    //     $orderInfo = "Thanh toán qua MoMo";
    //     $amount = "10000";
    //     $orderId = time() . "";
    //     $returnUrl = "http://api.course-selling.id.vn/api/order/redirect-notification";
    //     $notifyurl = "http://api.course-selling.id.vn/api/order/payment-notification";
    //     // Lưu ý: link notifyUrl không phải là dạng localhost
    //     // $extraData = "merchantName=MoMo Partner";

    //     $requestId = time() . "";
    //     $requestType = "captureMoMoWallet";
    //     $extraData = "";
    //     //before sign HMAC SHA256 signature
    //     $rawHash = "partnerCode=" . $partnerCode . "&accessKey=" . $accessKey . "&requestId=" . $requestId . "&amount=" . $amount . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&returnUrl=" . $returnUrl . "&notifyUrl=" . $notifyurl . "&extraData=" . $extraData;
    //     $signature = hash_hmac("sha256", $rawHash, $secretKey);
    //     $data = array(
    //         'partnerCode' => $partnerCode,
    //         'accessKey' => $accessKey,
    //         'requestId' => $requestId,
    //         'amount' => $amount,
    //         'orderId' => $orderId,
    //         'orderInfo' => $orderInfo,
    //         'returnUrl' => $returnUrl,
    //         'notifyUrl' => $notifyurl,
    //         'extraData' => $extraData,
    //         'requestType' => $requestType,
    //         'signature' => $signature
    //     );
    //     $result = $this->execPostRequest($endpoint, json_encode($data));
    //     $jsonResult = json_decode($result, true);  // decode json

    //     dd($jsonResult);
    //     return $jsonResult;
    // }


    protected function config()
    {
        $config = '
                    {
                        "partnerCode": "MOMOBKUN20180529",
                        "accessKey": "klm05TvNBzhg7h7j",
                        "secretKey": "at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa"
                    }
                ';
        return json_decode($config, true);
    }

    // VNPAY
    public function vnpay($order)
    {
        $request = new Request();
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl =  "http://api.course-selling.id.vn/api/order/vnp-redirect";
        $vnp_TmnCode = "Z3TMZ8S8"; //Mã website tại VNPAY
        $vnp_HashSecret = "FITCAHVHWCWPWLVLHFTLWXLZIXCENEYI"; //Chuỗi bí mật

        $vnp_Amount = (int)$order->total_amount * 100;
        $vnp_BankCode = $request->input('bank_code') ? $request->input('bank_code') : 'NCB';
        // $vnp_BankCode = 'VNBANK';
        $vnp_TxnRef = $order->order_id;
        $vnp_OrderInfo = "Thanh toán đơn hàng khóa học";
        $vnp_Locale = 'vn';
        $vnp_CurrCode = 'VND';
        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => $vnp_CurrCode,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => "Giáo dục",
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_BankCode" => $vnp_BankCode,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_IpAddr" => $_SERVER['REMOTE_ADDR'],

        );
        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret); //
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }
        $returnData = array(
            'code' => '00', 'message' => 'success', 'payUrl' => $vnp_Url
        );

        return $returnData;
    }

    public function apn(Request $request)
    {

        return response()->json([
            'page' => 'apn',
            'data' => $request->input()
        ], 200);
    }
    public function vmp_apn(Request $request)
    {

        return response()->json([
            'page' => 'vmp_apn',
            'data' => $request->input()
        ], 200);
    }
    public function vnp_return(Request $request)
    {
        $vnp_HashSecret = "FITCAHVHWCWPWLVLHFTLWXLZIXCENEYI"; //Chuỗi bí mật

        $vnp_SecureHash = $request->input('vnp_SecureHash');
        $inputData = array();
        foreach ($request->input() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        $orderId = $request->input('vnp_TxnRef');
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        if ($secureHash == $vnp_SecureHash) {
            if ($request->input('vnp_ResponseCode') == '00') {
                // echo "GD Thanh cong";

                $data = [
                    'Payment_status' => $request->input('vnp_TransactionStatus'),
                    'order_id' => $orderId,
                    'amount' => $request->input('vnp_Amount'),
                    'Payment_method' => $request->input('vnp_CardType')
                ];
                order::where('order_id', $orderId)->first()->update(['order_status' => 1, 'checkoutUrl' => 'done']);
                DB::table('logs')->insert(['log' => json_encode($data)]);
            } else {
                $data = [
                    'danger' => "Giao dịch thất bại",
                    'order_id' => $orderId,
                ];
                DB::table('logs')->insert(['log' => json_encode($data)]);
            }
        } else {
            $data = [
                'danger' => "Giao dịch này có thể bị hack, vui lòng kiểm tra chữ ký của bạn và trả lại chữ ký",
                'order_id' => $orderId,
            ];
            DB::table('logs')->insert(['log' => json_encode($data)]);
        }


        $debugger = array();

        return view('success', [
            'response' => [
                'resultCode' => $request->input('vnp_TransactionStatus') == "00" ? 0 : -99,
                'message' => $request->input('vnp_TransactionStatus') == "00" ? "Thanh toán thành công" : "Có xẩy ra lỗi khi thanh toán vui lòng thử lại sau",
                'orderId' => $orderId,
                'amount' => $request->input('vnp_Amount'),
                'payType' => "VNPAY " . $request->input('vnp_CardType')
            ],
            'debugger' => $debugger
        ]);
    }
}
