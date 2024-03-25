<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Notification</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>

<body>
    @php
        // dd($response);
    @endphp
    <div class="notification-container" id="notificationContainer">
        <div class="notification {{ $response['resultCode'] == 0 ? 'success' : 'danger' }}" id="notification">
            @if ($response['resultCode'] == 0)
            <p id="notificationMessage">Thanh toán thành công</p>
            @else
            <p id="notificationMessage">{{ $response['message'] }}</p>

            @endif
            <span class="close" id="close">&times;</span>
        </div>
    </div>
    {{--  --}}
    <div class="payment-success">
        <div class="payment-success-content">
            @if ($response['resultCode'] == 0)
                <h2>Thanh toán thành công</h2>
                <p>Cảm ơn bạn đã thanh toán. Chi tiết giao dịch của bạn:</p>
                <ul>
                    <li><strong>Mã đơn hàng:</strong> {{ $response['orderId'] }}</li>
                    <li><strong>Tổng tiền:</strong> {{ number_format($response['amount'], 0, 0) }}</li>
                    <li><strong>Phương thức thanh toán:</strong> {{ $response['payType'] }}</li>
                    <li><strong>Thời gian:</strong> {{ now() }}</li>
                </ul>
            @else
                <h2 style="color: #dd2121">{{ $response['message'] }}</h2>
                <ul>
                    <li><strong>Mã đơn hàng:</strong> {{ $response['orderId'] }}</li>
                    <li><strong>Tổng tiền:</strong> {{ number_format($response['amount'], 0, 0) }}</li>
                    <li><strong>Phương thức thanh toán:</strong> {{ $response['payType'] }}</li>
                    <li><strong>Thời gian:</strong> {{ now() }}</li>
                </ul>
            @endif
            <div class="btn">
                <a href="http://course-selling.id.vn" id="home">Trang chủ</a>
                <a href="http://course-selling.id.vn/" id="viewOrder">Xem đơn hàng</a>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/script.js') }}"></script>
</body>

</html>
