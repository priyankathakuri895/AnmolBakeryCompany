<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ExpenseCategory;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Van;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Expense::query()
            ->with('van')
            ->when($request->filled('van'), fn ($q) => $q->where('van_id', $request->integer('van')))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('expense_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('expense_date', '<=', $request->date('to')));

        $totalAmount = (clone $query)->sum('amount');

        $expenses = $query->orderByDesc('expense_date')->orderByDesc('id')->paginate(20);

        return view('admin.expenses.index', [
            'expenses' => $expenses,
            'vanOptions' => $this->vanOptions(),
            'categories' => ExpenseCategory::cases(),
            'totalAmount' => $totalAmount,
        ]);
    }

    public function create(): View
    {
        return view('admin.expenses.create', [
            'expense' => new Expense,
            'vanOptions' => $this->vanOptions(),
            'categories' => ExpenseCategory::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Expense::create($this->validated($request) + ['user_id' => $request->user()?->id]);

        return redirect()
            ->route('admin.expenses.index')
            ->with('success', 'Expense recorded.');
    }

    public function edit(Expense $expense): View
    {
        return view('admin.expenses.edit', [
            'expense' => $expense,
            'vanOptions' => $this->vanOptions(),
            'categories' => ExpenseCategory::cases(),
        ]);
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $expense->update($this->validated($request));

        return redirect()
            ->route('admin.expenses.index')
            ->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return redirect()
            ->route('admin.expenses.index')
            ->with('success', 'Expense deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'expense_date' => ['required', 'date'],
            'van_id' => ['nullable', 'exists:vans,id'],
            'category' => ['required', Rule::enum(ExpenseCategory::class)],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string'],
        ]);
    }

    private function vanOptions()
    {
        return Van::orderBy('name')->pluck('name', 'id');
    }
}
