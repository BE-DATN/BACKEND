<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class order extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'total_amount',
        'payment_method',
        'voucher',
        'order_status',
        'order_id',
        'checkoutUrl'
    ];

    public static function getSold30Day()
{
    $start_date = Carbon::now()->subDays(30);
    $end_date = Carbon::now();

    $sales_data = order_detail::whereHas('order', function ($query) use ($start_date, $end_date) {
        $query->whereBetween('created_at', [$start_date, $end_date]);
    })
        ->select('course_name', DB::raw('COUNT(*) as sales'))
        ->groupBy('course_name')
        ->orderBy('sales', 'desc')
        ->get();

    // Format kết quả theo yêu cầu
    $formatted_sales_data = $sales_data->map(function ($item) {
        return ['keys' => $item->course_name, 'values' => $item->sales];
    });

    return $formatted_sales_data;
}

public static function getRevenue30Day()
{
    $start_date = Carbon::now()->subDays(30);
    $end_date = Carbon::now()->endOfMonth();

    $revenue_data = self::whereBetween('created_at', [$start_date, $end_date])
        ->selectRaw('DATE(created_at) as day, SUM(total_amount) as revenue')
        ->where('order_status', 1)
        ->groupBy('day')
        ->orderBy('day')
        ->get();

    // Format kết quả theo yêu cầu
    $formatted_revenue_data = $revenue_data->map(function ($item) {
        return ['keys' => $item->day, 'values' => $item->revenue];
    });

    return $formatted_revenue_data;
}
}
