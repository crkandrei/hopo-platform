<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index()
    {
        $user = auth()->user();
        
        // SUPER_ADMIN vede toți utilizatorii
        // COMPANY_ADMIN vede doar utilizatorii din compania sa
        // STAFF nu are acces
        if ($user->isStaff()) {
            abort(403, 'Acces interzis');
        }
        
        $query = User::with(['role', 'company', 'location']);
        
        if ($user->isCompanyAdmin()) {
            // COMPANY_ADMIN vede doar utilizatorii din compania sa
            $query->where(function($q) use ($user) {
                $q->where('company_id', $user->company_id)
                  ->orWhereHas('location', function($lq) use ($user) {
                      $lq->where('company_id', $user->company_id);
                  });
            });
        }
        
        $users = $query->orderBy('name')->get();
        
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $currentUser = auth()->user();
        
        if ($currentUser->isStaff()) {
            abort(403, 'Acces interzis');
        }
        
        $roles = Role::all();
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $locations = Location::where('is_active', true)->orderBy('name')->get();
        
        // Dacă e COMPANY_ADMIN, arată doar compania sa și locațiile sale
        if ($currentUser->isCompanyAdmin()) {
            $companies = Company::where('id', $currentUser->company_id)->get();
            $locations = Location::where('company_id', $currentUser->company_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }
        
        return view('users.create', compact('roles', 'companies', 'locations'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $currentUser = auth()->user();
        
        if ($currentUser->isStaff()) {
            abort(403, 'Acces interzis');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|min:3|max:255|regex:/^[a-zA-Z0-9_]+$/|unique:users,username',
            'email' => 'nullable|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'company_id' => [
                'nullable',
                'exists:companies,id',
                function ($attribute, $value, $fail) use ($currentUser) {
                    if ($currentUser->isCompanyAdmin() && $value != $currentUser->company_id) {
                        $fail('Nu puteți crea utilizatori pentru alte companii.');
                    }
                },
            ],
            'location_id' => [
                'nullable',
                'exists:locations,id',
                function ($attribute, $value, $fail) use ($request, $currentUser) {
                    if ($value) {
                        $location = Location::find($value);
                        if ($currentUser->isCompanyAdmin() && $location && $location->company_id != $currentUser->company_id) {
                            $fail('Nu puteți crea utilizatori pentru alte companii.');
                        }
                        if ($request->company_id && $location && $location->company_id != $request->company_id) {
                            $fail('Locația trebuie să aparțină companiei selectate.');
                        }
                    }
                },
            ],
            'status' => 'required|in:active,inactive',
        ]);
        
        // Verificări suplimentare pentru roluri
        $selectedRole = Role::find($validated['role_id']);
        
        if ($selectedRole->name === 'SUPER_ADMIN' && !$currentUser->isSuperAdmin()) {
            return back()->withErrors(['role_id' => 'Doar SUPER_ADMIN poate crea alți SUPER_ADMIN.'])->withInput();
        }
        
        if ($selectedRole->name === 'SUPER_ADMIN') {
            $validated['company_id'] = null;
            $validated['location_id'] = null;
        } elseif ($selectedRole->name === 'COMPANY_ADMIN') {
            $validated['location_id'] = null; // COMPANY_ADMIN nu are locație specifică
            if (!$validated['company_id']) {
                return back()->withErrors(['company_id' => 'COMPANY_ADMIN trebuie să aibă o companie asociată.'])->withInput();
            }
        } elseif ($selectedRole->name === 'STAFF') {
            if (!$validated['location_id']) {
                return back()->withErrors(['location_id' => 'STAFF trebuie să aibă o locație asociată.'])->withInput();
            }
            // Setează company_id din locație
            $location = Location::find($validated['location_id']);
            $validated['company_id'] = $location->company_id;
        }
        
        $validated['password'] = Hash::make($validated['password']);
        
        User::create($validated);
        
        return redirect()->route('users.index')
            ->with('success', 'Utilizatorul a fost creat cu succes');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $currentUser = auth()->user();
        
        if ($currentUser->isStaff()) {
            abort(403, 'Acces interzis');
        }
        
        // Verifică dacă utilizatorul curent poate vedea acest utilizator
        if ($currentUser->isCompanyAdmin()) {
            $userCompanyId = $user->company_id ?? $user->location?->company_id;
            if ($userCompanyId != $currentUser->company_id) {
                abort(403, 'Acces interzis');
            }
        }
        
        $user->load(['role', 'company', 'location']);
        
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $currentUser = auth()->user();
        
        if ($currentUser->isStaff()) {
            abort(403, 'Acces interzis');
        }
        
        // Verifică dacă utilizatorul curent poate edita acest utilizator
        if ($currentUser->isCompanyAdmin()) {
            $userCompanyId = $user->company_id ?? $user->location?->company_id;
            if ($userCompanyId != $currentUser->company_id) {
                abort(403, 'Acces interzis');
            }
        }
        
        $roles = Role::all();
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $locations = Location::where('is_active', true)->orderBy('name')->get();
        
        if ($currentUser->isCompanyAdmin()) {
            $companies = Company::where('id', $currentUser->company_id)->get();
            $locations = Location::where('company_id', $currentUser->company_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }
        
        $user->load(['role', 'company', 'location']);
        
        return view('users.edit', compact('user', 'roles', 'companies', 'locations'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();
        
        if ($currentUser->isStaff()) {
            abort(403, 'Acces interzis');
        }
        
        // Verifică dacă utilizatorul curent poate edita acest utilizator
        if ($currentUser->isCompanyAdmin()) {
            $userCompanyId = $user->company_id ?? $user->location?->company_id;
            if ($userCompanyId != $currentUser->company_id) {
                abort(403, 'Acces interzis');
            }
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-zA-Z0-9_]+$/',
                Rule::unique('users')->ignore($user->id),
            ],
            'email' => 'nullable|email|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'company_id' => [
                'nullable',
                'exists:companies,id',
                function ($attribute, $value, $fail) use ($currentUser) {
                    if ($currentUser->isCompanyAdmin() && $value != $currentUser->company_id) {
                        $fail('Nu puteți modifica utilizatori pentru alte companii.');
                    }
                },
            ],
            'location_id' => [
                'nullable',
                'exists:locations,id',
                function ($attribute, $value, $fail) use ($request, $currentUser) {
                    if ($value) {
                        $location = Location::find($value);
                        if ($currentUser->isCompanyAdmin() && $location && $location->company_id != $currentUser->company_id) {
                            $fail('Nu puteți modifica utilizatori pentru alte companii.');
                        }
                        if ($request->company_id && $location && $location->company_id != $request->company_id) {
                            $fail('Locația trebuie să aparțină companiei selectate.');
                        }
                    }
                },
            ],
            'status' => 'required|in:active,inactive',
        ]);
        
        // Verificări suplimentare pentru roluri
        $selectedRole = Role::find($validated['role_id']);
        
        if ($selectedRole->name === 'SUPER_ADMIN' && !$currentUser->isSuperAdmin()) {
            return back()->withErrors(['role_id' => 'Doar SUPER_ADMIN poate atribui rolul SUPER_ADMIN.'])->withInput();
        }
        
        if ($selectedRole->name === 'SUPER_ADMIN') {
            $validated['company_id'] = null;
            $validated['location_id'] = null;
        } elseif ($selectedRole->name === 'COMPANY_ADMIN') {
            $validated['location_id'] = null;
            if (!$validated['company_id']) {
                return back()->withErrors(['company_id' => 'COMPANY_ADMIN trebuie să aibă o companie asociată.'])->withInput();
            }
        } elseif ($selectedRole->name === 'STAFF') {
            if (!$validated['location_id']) {
                return back()->withErrors(['location_id' => 'STAFF trebuie să aibă o locație asociată.'])->withInput();
            }
            $location = Location::find($validated['location_id']);
            $validated['company_id'] = $location->company_id;
        }
        
        // Actualizează parola doar dacă este furnizată
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        
        $user->update($validated);
        
        return redirect()->route('users.index')
            ->with('success', 'Utilizatorul a fost actualizat cu succes');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        $currentUser = auth()->user();
        
        if ($currentUser->isStaff()) {
            abort(403, 'Acces interzis');
        }
        
        // Verifică dacă utilizatorul curent poate șterge acest utilizator
        if ($currentUser->isCompanyAdmin()) {
            $userCompanyId = $user->company_id ?? $user->location?->company_id;
            if ($userCompanyId != $currentUser->company_id) {
                abort(403, 'Acces interzis');
            }
        }
        
        // Nu permite ștergerea propriului cont
        if ($user->id === $currentUser->id) {
            return redirect()->route('users.index')
                ->with('error', 'Nu puteți șterge propriul cont');
        }
        
        $user->delete();
        
        return redirect()->route('users.index')
            ->with('success', 'Utilizatorul a fost șters cu succes');
    }
}
