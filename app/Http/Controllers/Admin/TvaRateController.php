<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TvaRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TvaRateController extends Controller
{
    public function index()
    {
        $this->authorizeSuperAdmin();

        $rates = TvaRate::orderBy('vat_class')->get();

        return view('admin.tva-rates.index', compact('rates'));
    }

    public function create()
    {
        $this->authorizeSuperAdmin();

        return view('admin.tva-rates.create');
    }

    public function store(Request $request)
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'percentage' => 'required|numeric|min:0|max:100',
            'vat_class'  => 'required|integer|min:1|max:9|unique:tva_rates,vat_class',
            'is_active'  => 'boolean',
        ]);

        TvaRate::create([
            'name'       => $validated['name'],
            'percentage' => $validated['percentage'],
            'vat_class'  => $validated['vat_class'],
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.tva-rates.index')
            ->with('success', "Cota TVA «{$validated['name']}» a fost creată.");
    }

    public function edit(TvaRate $tvaRate)
    {
        $this->authorizeSuperAdmin();

        return view('admin.tva-rates.edit', compact('tvaRate'));
    }

    public function update(Request $request, TvaRate $tvaRate)
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'percentage' => 'required|numeric|min:0|max:100',
            'vat_class'  => 'required|integer|min:1|max:9|unique:tva_rates,vat_class,' . $tvaRate->id,
            'is_active'  => 'boolean',
        ]);

        $tvaRate->update([
            'name'       => $validated['name'],
            'percentage' => $validated['percentage'],
            'vat_class'  => $validated['vat_class'],
            'is_active'  => $request->boolean('is_active', false),
        ]);

        return redirect()->route('admin.tva-rates.index')
            ->with('success', "Cota TVA «{$tvaRate->name}» a fost actualizată.");
    }

    public function destroy(TvaRate $tvaRate)
    {
        $this->authorizeSuperAdmin();

        if ($tvaRate->products()->exists()) {
            return redirect()->route('admin.tva-rates.index')
                ->with('error', "Cota TVA «{$tvaRate->name}» nu poate fi ștearsă deoarece este folosită de produse.");
        }

        $name = $tvaRate->name;
        $tvaRate->delete();

        return redirect()->route('admin.tva-rates.index')
            ->with('success', "Cota TVA «{$name}» a fost ștearsă.");
    }

    private function authorizeSuperAdmin(): void
    {
        if (!Auth::user()?->isSuperAdmin()) {
            abort(403, 'Acces interzis.');
        }
    }
}
