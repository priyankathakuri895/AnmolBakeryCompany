<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Van;
use App\Models\VanSettlement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->filled('from') ? Carbon::parse($request->string('from')) : now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->string('to')) : now()->endOfMonth();

        $salesByVan = VanSettlement::query()
            ->finalized()
            ->whereHas('vanLoad', fn ($q) => $q->whereBetween('load_date', [$from->toDateString(), $to->toDateString()]))
            ->with(['vanLoad.van', 'items'])
            ->get()
            ->groupBy(fn (VanSettlement $s) => $s->vanLoad->van->name)
            ->map(fn ($group) => [
                'van' => $group->first()->vanLoad->van->name,
                'settlements' => $group->count(),
                'total_sales' => $group->sum(fn (VanSettlement $s) => $s->totalSalesValue()),
                'total_cash' => (float) $group->sum('cash_collected'),
                'total_online' => (float) $group->sum('online_collected'),
                'net_debit_change' => $group->sum(fn (VanSettlement $s) => $s->netDebitChange()),
            ])
            ->values();

        $totalSales = $salesByVan->sum('total_sales');

        $expensesByCategory = Expense::query()
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get();

        $totalExpenses = (float) $expensesByCategory->sum('total');

        $totalDebitOutstanding = (float) Van::sum('current_debit_balance');

        $stockValuation = Product::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => [
                'product' => $product,
                'value' => (float) $product->current_stock * (float) $product->price,
            ]);

        $totalStockValue = $stockValuation->sum('value');

        return view('admin.reports.index', [
            'from' => $from,
            'to' => $to,
            'salesByVan' => $salesByVan,
            'totalSales' => $totalSales,
            'expensesByCategory' => $expensesByCategory,
            'totalExpenses' => $totalExpenses,
            'totalDebitOutstanding' => $totalDebitOutstanding,
            'stockValuation' => $stockValuation,
            'totalStockValue' => $totalStockValue,
        ]);
    }
}
