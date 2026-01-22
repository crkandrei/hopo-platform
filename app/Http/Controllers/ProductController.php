<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of products for the tenant.
     */
    public function index()
    {
        // STAFF nu are acces la produse
        if (Auth::user()->isStaff()) {
            abort(403, 'Acces interzis');
        }
        
        $user = Auth::user();
        $location = $user->location;
        
        if (!$location) {
            return redirect($this->getHomeRoute())->with('error', 'Utilizatorul nu este asociat cu nicio locație');
        }

        $products = Product::where('location_id', $location->id)
            ->orderBy('name')
            ->get();

        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        // STAFF nu are acces la produse
        if (Auth::user()->isStaff()) {
            abort(403, 'Acces interzis');
        }
        
        $user = Auth::user();
        $location = $user->location;
        
        if (!$location) {
            return redirect($this->getHomeRoute())->with('error', 'Utilizatorul nu este asociat cu nicio locație');
        }

        return view('products.create');
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        // STAFF nu are acces la produse
        if (Auth::user()->isStaff()) {
            abort(403, 'Acces interzis');
        }
        
        $user = Auth::user();
        $location = $user->location;
        
        if (!$location) {
            return ApiResponder::error('Utilizatorul nu este asociat cu nicio locație', 400);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        try {
            $product = Product::create([
                'location_id' => $location->id,
                'name' => $validated['name'],
                'price' => $validated['price'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            return redirect()->route('products.index')
                ->with('success', 'Produsul a fost creat cu succes');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Eroare la crearea produsului: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        // STAFF nu are acces la produse
        if (Auth::user()->isStaff()) {
            abort(403, 'Acces interzis');
        }
        
        $user = Auth::user();
        $location = $user->location;
        
        if (!$location) {
            return redirect($this->getHomeRoute())->with('error', 'Utilizatorul nu este asociat cu nicio locație');
        }

        $product = Product::where('id', $id)
            ->where('location_id', $location->id)
            ->firstOrFail();

        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit($id)
    {
        // STAFF nu are acces la produse
        if (Auth::user()->isStaff()) {
            abort(403, 'Acces interzis');
        }
        
        $user = Auth::user();
        $location = $user->location;
        
        if (!$location) {
            return redirect($this->getHomeRoute())->with('error', 'Utilizatorul nu este asociat cu nicio locație');
        }

        $product = Product::where('id', $id)
            ->where('location_id', $location->id)
            ->firstOrFail();

        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, $id)
    {
        // STAFF nu are acces la produse
        if (Auth::user()->isStaff()) {
            abort(403, 'Acces interzis');
        }
        
        $user = Auth::user();
        $location = $user->location;
        
        if (!$location) {
            return ApiResponder::error('Utilizatorul nu este asociat cu nicio locație', 400);
        }

        $product = Product::where('id', $id)
            ->where('location_id', $location->id)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        try {
            $product->update([
                'name' => $validated['name'],
                'price' => $validated['price'],
                'is_active' => $validated['is_active'] ?? $product->is_active,
            ]);

            return redirect()->route('products.index')
                ->with('success', 'Produsul a fost actualizat cu succes');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Eroare la actualizarea produsului: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy($id)
    {
        // STAFF nu are acces la produse
        if (Auth::user()->isStaff()) {
            abort(403, 'Acces interzis');
        }
        
        $user = Auth::user();
        $location = $user->location;
        
        if (!$location) {
            return ApiResponder::error('Utilizatorul nu este asociat cu nicio locație', 400);
        }

        $product = Product::where('id', $id)
            ->where('location_id', $location->id)
            ->firstOrFail();

        try {
            // Verifică dacă produsul este folosit în sesiuni
            $usedInSessions = $product->playSessionProducts()->exists();
            
            if ($usedInSessions) {
                // Dacă este folosit, doar îl dezactivăm
                $product->update(['is_active' => false]);
                return redirect()->route('products.index')
                    ->with('success', 'Produsul a fost dezactivat (este folosit în sesiuni)');
            }

            // Dacă nu este folosit, îl ștergem
            $product->delete();

            return redirect()->route('products.index')
                ->with('success', 'Produsul a fost șters cu succes');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Eroare la ștergerea produsului: ' . $e->getMessage());
        }
    }
}
