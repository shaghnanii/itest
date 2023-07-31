<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Order;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {}

    /**
     * Useful order statistics for the merchant API.
     *
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form
     * {count: total number of orders in range,
     * commission_owed: amount of unpaid commissions for orders with an affiliate,
     * revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        try {
            $orders_count = Order::query()->whereBetween('created_at', [$request->from, $request->to])->count();
            $revenue = Order::query()->whereBetween('created_at', [$request->from, $request->to])->sum('subtotal');
            $total_unpaid_commission = Order::query()->whereBetween('created_at', [$request->from, $request->to])->where('affiliate_id', '!=', null)->sum('commission_owed');
            return response()->json([
                "count" => $orders_count,
                "commissions_owed" => $total_unpaid_commission,
                "revenue" => $revenue,
            ]);
        }
        catch (\Exception $exception) {
            return response()->json($exception->getMessage());
        }
    }
}
