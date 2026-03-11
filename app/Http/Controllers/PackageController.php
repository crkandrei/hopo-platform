<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index(Location $location)
    {
        $this->authorize('view', $location);
        $packages = $location->packages()->orderBy('name')->get();
        return view('packages.index', compact('location', 'packages'));
    }

    public function create(Location $location)
    {
        $this->authorize('update', $location);
        return view('packages.create', compact('location'));
    }

    public function store(Request $request, Location $location)
    {
        $this->authorize('update', $location);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:65535',
            'price' => 'required|numeric|min:0|max:999999.99',
            'is_active' => 'boolean',
        ]);
        $location->packages()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'is_active' => $request->boolean('is_active', true),
        ]);
        return redirect()
            ->route('locations.packages.index', $location)
            ->with('success', 'Pachetul a fost adăugat.');
    }

    public function edit(Location $location, Package $package)
    {
        $this->authorize('update', $location);
        if ($package->location_id !== $location->id) {
            abort(404);
        }
        return view('packages.edit', compact('location', 'package'));
    }

    public function update(Request $request, Location $location, Package $package)
    {
        $this->authorize('update', $location);
        if ($package->location_id !== $location->id) {
            abort(404);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:65535',
            'price' => 'required|numeric|min:0|max:999999.99',
            'is_active' => 'boolean',
        ]);
        $package->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'is_active' => $request->boolean('is_active', $package->is_active),
        ]);
        return redirect()
            ->route('locations.packages.index', $location)
            ->with('success', 'Pachetul a fost actualizat.');
    }

    public function destroy(Location $location, Package $package)
    {
        $this->authorize('update', $location);
        if ($package->location_id !== $location->id) {
            abort(404);
        }
        $package->delete();
        return redirect()
            ->route('locations.packages.index', $location)
            ->with('success', 'Pachetul a fost șters.');
    }
}
