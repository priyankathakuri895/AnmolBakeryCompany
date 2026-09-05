<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Van;
use App\Models\VanDebitTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VanDebitTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $transactions = VanDebitTransaction::query()
            ->with('van')
            ->when($request->filled('van'), fn ($q) => $q->where('van_id', $request->integer('van')))
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.van-debit.index', [
            'transactions' => $transactions,
            'vans' => Van::orderBy('name')->get(['id', 'name', 'current_debit_balance']),
            'vanOptions' => $this->vanOptions(),
        ]);
    }

    public function create(): View
    {
        return view('admin.van-debit.create', [
            'vanOptions' => $this->vanOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'van_id' => ['required', 'exists:vans,id'],
            'direction' => ['required', 'in:increase,decrease'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transaction_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $signedAmount = $data['direction'] === 'increase' ? (float) $data['amount'] : -(float) $data['amount'];

        DB::transaction(function () use ($data, $signedAmount, $request) {
            $van = Van::findOrFail($data['van_id']);
            $newBalance = (float) $van->current_debit_balance + $signedAmount;

            VanDebitTransaction::create([
                'van_id' => $van->id,
                'amount' => $signedAmount,
                'balance_after' => $newBalance,
                'transaction_date' => $data['transaction_date'],
                'notes' => $data['notes'] ?? null,
                'user_id' => $request->user()?->id,
            ]);

            $van->update(['current_debit_balance' => $newBalance]);
        });

        return redirect()
            ->route('admin.van-debit.index')
            ->with('success', 'Debit adjustment recorded.');
    }

    private function vanOptions()
    {
        return Van::active()->orderBy('name')->pluck('name', 'id');
    }
}
