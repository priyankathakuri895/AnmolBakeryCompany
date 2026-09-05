<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReceiptStatus;
use App\Enums\StockCheckStatus;
use App\Http\Controllers\Controller;
use App\Models\MaterialReceipt;
use App\Models\Product;
use App\Models\ProductStockCheck;
use App\Models\RawMaterial;
use App\Models\Salesman;
use App\Models\Supplier;
use App\Models\SupplierVehicle;
use App\Models\Van;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $materials = RawMaterial::query()
            ->orderBy('name')
            ->get();

        $suppliers = Supplier::query()
            ->with('vehicles')
            ->withCount('receipts')
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->orderBy('name')
            ->get();

        $vans = Van::query()
            ->with('defaultSalesman')
            ->orderBy('name')
            ->get();

        return view('admin.dashboard', [
            'materials' => $materials,
            'suppliers' => $suppliers,
            'products' => $products,
            'vans' => $vans,
            'supplierCount' => $suppliers->count(),
            'activeSupplierCount' => $suppliers->where('is_active', true)->count(),
            'vehicleCount' => SupplierVehicle::count(),
            'materialCount' => $materials->count(),
            'activeMaterialCount' => $materials->where('is_active', true)->count(),
            'lowStockCount' => $materials->filter->isBelowReorderLevel()->count(),
            'pendingReceiptCount' => MaterialReceipt::where('status', ReceiptStatus::Pending)->count(),
            'productCount' => $products->count(),
            'activeProductCount' => $products->where('is_active', true)->count(),
            'lowStockProductCount' => $products->filter->isBelowReorderLevel()->count(),
            'vanCount' => $vans->count(),
            'activeVanCount' => $vans->where('is_active', true)->count(),
            'salesmanCount' => Salesman::count(),
            'openStockCheckCount' => ProductStockCheck::where('status', StockCheckStatus::Draft)->count(),
        ]);
    }
}
