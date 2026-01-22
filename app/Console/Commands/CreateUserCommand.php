<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\Location;
use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creează un utilizator nou interactiv (fără înregistrare publică)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('═══════════════════════════════════════════════════════');
        $this->info('  Creare Utilizator Nou');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        // 1. Username
        $username = $this->ask('Username');
        
        // Validare username
        $validator = Validator::make(['username' => $username], [
            'username' => 'required|string|min:3|max:255|regex:/^[a-zA-Z0-9_]+$/',
        ]);
        
        if ($validator->fails()) {
            $this->error('Username invalid! Trebuie să conțină doar litere, cifre și underscore, minim 3 caractere.');
            return Command::FAILURE;
        }

        // Verifică dacă utilizatorul există deja
        $existingUser = User::with(['role', 'location', 'company'])->where('username', $username)->first();
        if ($existingUser) {
            if (!$this->confirm("Utilizatorul cu username '{$username}' există deja. Vrei să-l actualizezi?", false)) {
                $this->info('Operațiune anulată.');
                return Command::SUCCESS;
            }
        }

        // 2. Nume
        $name = $this->ask('Nume complet', $existingUser ? $existingUser->name : null);
        
        // 3. Email (opțional, pentru notificări)
        $email = $this->ask('Email (opțional, pentru notificări)', $existingUser ? $existingUser->email : null);
        
        if ($email) {
            $emailValidator = Validator::make(['email' => $email], [
                'email' => 'email',
            ]);
            
            if ($emailValidator->fails()) {
                $this->error('Email invalid! Va fi ignorat.');
                $email = null;
            }
        }

        // 4. Rol
        $roles = Role::all(['id', 'name', 'display_name']);
        
        if ($roles->isEmpty()) {
            $this->error('Nu există roluri în sistem! Rulează mai întâi: php artisan db:seed --class=RoleSeeder');
            return Command::FAILURE;
        }

        $roleOptions = $roles->map(function ($role) {
            return "{$role->display_name} ({$role->name})";
        })->toArray();

        $defaultRoleIndex = null;
        if ($existingUser && $existingUser->role) {
            $roleString = $existingUser->role->display_name . ' (' . $existingUser->role->name . ')';
            $defaultRoleIndex = array_search($roleString, $roleOptions);
        }

        $selectedRoleOption = $this->choice(
            'Selectează rolul',
            $roleOptions,
            $defaultRoleIndex !== false ? $defaultRoleIndex : null
        );

        $selectedRoleIndex = array_search($selectedRoleOption, $roleOptions);
        $selectedRole = $roles[$selectedRoleIndex];

        // 5. Company și Location (doar dacă nu e SUPER_ADMIN)
        $companyId = null;
        $locationId = null;
        $locationName = null;

        if ($selectedRole->name !== 'SUPER_ADMIN') {
            // Select Company first
            $companies = Company::all(['id', 'name']);
            
            if ($companies->isEmpty()) {
                $this->error('Nu există companii în sistem! Creează mai întâi o companie.');
                return Command::FAILURE;
            }

            $companyOptions = $companies->map(function ($company) {
                return $company->name;
            })->toArray();

            $defaultCompanyIndex = null;
            if ($existingUser && $existingUser->company) {
                $defaultCompanyIndex = array_search($existingUser->company->name, $companyOptions);
            }

            $selectedCompanyOption = $this->choice(
                'Selectează compania',
                $companyOptions,
                $defaultCompanyIndex !== false ? $defaultCompanyIndex : null
            );

            $selectedCompanyIndex = array_search($selectedCompanyOption, $companyOptions);
            $selectedCompany = $companies[$selectedCompanyIndex];
            $companyId = $selectedCompany->id;

            // Select Location
            $locations = Location::where('company_id', $companyId)->get(['id', 'name']);
            
            if ($locations->isEmpty()) {
                $this->error('Nu există locații pentru această companie! Creează mai întâi o locație.');
                return Command::FAILURE;
            }

            $locationOptions = $locations->map(function ($location) {
                return $location->name;
            })->toArray();

            $defaultLocationIndex = null;
            if ($existingUser && $existingUser->location) {
                $defaultLocationIndex = array_search($existingUser->location->name, $locationOptions);
            }

            $selectedLocationOption = $this->choice(
                'Selectează locația',
                $locationOptions,
                $defaultLocationIndex !== false ? $defaultLocationIndex : null
            );

            $selectedLocationIndex = array_search($selectedLocationOption, $locationOptions);
            $selectedLocation = $locations[$selectedLocationIndex];
            $locationId = $selectedLocation->id;
            $locationName = $selectedLocation->name;
        }

        // 6. Parolă
        $password = $this->secret('Parolă (minim 8 caractere)');
        
        if (strlen($password) < 8) {
            $this->error('Parola trebuie să aibă minim 8 caractere!');
            return Command::FAILURE;
        }

        $confirmPassword = $this->secret('Confirmă parola');
        
        if ($password !== $confirmPassword) {
            $this->error('Parolele nu se potrivesc!');
            return Command::FAILURE;
        }

        // 7. Status (opțional)
        $status = $this->choice(
            'Status utilizator',
            ['active', 'inactive'],
            $existingUser ? $existingUser->status : 'active'
        );

        // 8. Confirmare
        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════');
        $this->info('  Rezumat');
        $this->info('═══════════════════════════════════════════════════════');
        $this->table(
            ['Câmp', 'Valoare'],
            [
                ['Username', $username],
                ['Nume', $name],
                ['Email', $email ?: 'N/A'],
                ['Rol', $selectedRole->display_name . ' (' . $selectedRole->name . ')'],
                ['Companie', $companyId ? Company::find($companyId)->name : 'N/A (Super Admin)'],
                ['Locație', $locationName ?: 'N/A (Super Admin)'],
                ['Status', $status],
            ]
        );

        if (!$this->confirm('Confirmi crearea/actualizarea acestui utilizator?', true)) {
            $this->info('Operațiune anulată.');
            return Command::SUCCESS;
        }

        // 9. Creează sau actualizează utilizatorul
        try {
            if ($existingUser) {
                $existingUser->update([
                    'name' => $name,
                    'username' => $username,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'role_id' => $selectedRole->id,
                    'company_id' => $companyId,
                    'location_id' => $locationId,
                    'status' => $status,
                    'email_verified_at' => $email ? now() : null,
                ]);
                
                $this->newLine();
                $this->info('✅ Utilizator actualizat cu succes!');
            } else {
                User::create([
                    'name' => $name,
                    'username' => $username,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'role_id' => $selectedRole->id,
                    'company_id' => $companyId,
                    'location_id' => $locationId,
                    'status' => $status,
                    'email_verified_at' => $email ? now() : null,
                ]);
                
                $this->newLine();
                $this->info('✅ Utilizator creat cu succes!');
            }

            $this->newLine();
            $this->table(
                ['Câmp', 'Valoare'],
                [
                    ['Username', $username],
                    ['Parolă', $password],
                ]
            );
            $this->warn('⚠️  Notează aceste credențiale într-un loc sigur!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Eroare la crearea utilizatorului: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
