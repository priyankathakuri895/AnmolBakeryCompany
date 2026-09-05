<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Salesman;
use App\Models\Van;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VanController extends Controller
{
    public function index(Request $request): View
    {
        $vans = Van::query()
            ->with('defaultSalesman')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search')->trim().'%';

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', $search)->orWhere('registration_number', 'like', $search);
                });
            })
            ->when($request->input('status') === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->input('status') === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(15);

        return view('admin.vans.index', [
            'vans' => $vans,
            'salesmenOptions' => $this->salesmenOptions(),
        ]);
    }

    public function create(): View
    {
        return view('admin.vans.create', [
            'van' => new Van,
            'salesmenOptions' => $this->salesmenOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Van::create($this->validated($request));

        return redirect()
            ->route('admin.vans.index')
            ->with('success', 'Van added.');
    }

    public function edit(Van $van): View
    {
        return view('admin.vans.edit', [
            'van' => $van,
            'salesmenOptions' => $this->salesmenOptions(),
        ]);
    }

    public function update(Request $request, Van $van): RedirectResponse
    {
        $van->update($this->validated($request, $van));

        return redirect()
            ->route('admin.vans.index')
            ->with('success', 'Van updated.');
    }

    public function destroy(Van $van): RedirectResponse
    {
        if ($van->vanLoads()->exists() || $van->debitTransactions()->exists()) {
            return back()->with(
                'error',
                'This van has loading or debit history, so it cannot be deleted. Mark it inactive instead.'
            );
        }

        $van->delete();

        return redirect()
            ->route('admin.vans.index')
            ->with('success', 'Van deleted.');
    }

    private function validated(Request $request, ?Van $van = null): array
    {
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('vans', 'name')->ignore($van?->id),
            ],
            'registration_number' => [
                'nullable', 'string', 'max:50',
                Rule::unique('vans', 'registration_number')->ignore($van?->id),
            ],
            'default_salesman_id' => ['nullable', 'exists:salesmen,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function salesmenOptions()
    {
        return Salesman::orderBy('name')->pluck('name', 'id');
    }
}
