<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Salesman;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SalesmanController extends Controller
{
    public function index(Request $request): View
    {
        $salesmen = Salesman::query()
            ->withCount('vans')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search')->trim().'%';

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', $search)->orWhere('phone', 'like', $search);
                });
            })
            ->when($request->input('status') === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->input('status') === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(15);

        return view('admin.salesmen.index', compact('salesmen'));
    }

    public function create(): View
    {
        return view('admin.salesmen.create', [
            'salesman' => new Salesman,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('id_document')) {
            $data['id_document_path'] = $request->file('id_document')->store('salesmen', 'public');
        }

        Salesman::create($data);

        return redirect()
            ->route('admin.salesmen.index')
            ->with('success', 'Salesman added.');
    }

    public function edit(Salesman $salesman): View
    {
        return view('admin.salesmen.edit', compact('salesman'));
    }

    public function update(Request $request, Salesman $salesman): RedirectResponse
    {
        $data = $this->validated($request, $salesman);

        if ($request->hasFile('id_document')) {
            if ($salesman->id_document_path) {
                Storage::disk('public')->delete($salesman->id_document_path);
            }

            $data['id_document_path'] = $request->file('id_document')->store('salesmen', 'public');
        }

        $salesman->update($data);

        return redirect()
            ->route('admin.salesmen.index')
            ->with('success', 'Salesman updated.');
    }

    public function destroy(Salesman $salesman): RedirectResponse
    {
        if ($salesman->vans()->exists() || $salesman->vanLoads()->exists()) {
            return back()->with(
                'error',
                'This salesman is assigned to a van or has loading history, so they cannot be deleted. Mark them inactive instead.'
            );
        }

        $salesman->delete();

        return redirect()
            ->route('admin.salesmen.index')
            ->with('success', 'Salesman deleted.');
    }

    private function validated(Request $request, ?Salesman $salesman = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'id_document' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        unset($data['id_document']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
