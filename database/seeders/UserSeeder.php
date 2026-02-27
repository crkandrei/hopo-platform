<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Exemplu de utilizare:
     * php artisan db:seed --class=UserSeeder
     *
     * Sau pentru un utilizator specific din tinker:
     * php artisan tinker
     * >>> $s = new \Database\Seeders\UserSeeder();
     * >>> $s->createUser('test_staff', 'Test', 'STAFF', 'slug-locatie', 'parola123');
     */
    public function run(): void
    {
        $this->command?->info('UserSeeder - Creează utilizatori pentru platformă');
        $this->command?->info('Utilizare: php artisan db:seed --class=UserSeeder');
        $this->command?->info('');
        $this->command?->info('Pentru a crea utilizatori individuali, folosește tinker:');
        $this->command?->info('php artisan tinker');
        $this->command?->info('>>> $seeder = new \Database\Seeders\UserSeeder();');
        $this->command?->info('>>> $seeder->createUser(\'username\', \'Nume\', \'COMPANY_ADMIN\', \'location-slug\', \'parola\');');
    }

    /**
     * Creează un utilizator nou
     *
     * @param string $username Username-ul utilizatorului
     * @param string $name Numele utilizatorului
     * @param string $roleName Numele rolului (SUPER_ADMIN, COMPANY_ADMIN, STAFF)
     * @param string|null $locationSlug Slug-ul locației (null pentru SUPER_ADMIN)
     * @param string $password Parola utilizatorului
     * @param string|null $email Email-ul utilizatorului (opțional)
     * @param string $status Statusul utilizatorului (default: 'active')
     * @return User
     */
    public function createUser(
        string $username,
        string $name,
        string $roleName,
        ?string $locationSlug = null,
        string $password = 'password123',
        ?string $email = null,
        string $status = 'active'
    ): User {
        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            throw new \Exception("Rolul '{$roleName}' nu a fost găsit. Rulează mai întâi RoleSeeder.");
        }

        $companyId = null;
        $locationId = null;

        if ($roleName === 'SUPER_ADMIN') {
            // ambele rămân null
        } elseif ($roleName === 'COMPANY_ADMIN') {
            if (!$locationSlug) {
                throw new \Exception("Pentru rolul 'COMPANY_ADMIN', trebuie să specifici un location slug pentru a determina compania.");
            }
            $location = Location::where('slug', $locationSlug)->firstOrFail();
            $companyId = $location->company_id;
            // location_id rămâne null
        } elseif ($roleName === 'STAFF') {
            if (!$locationSlug) {
                throw new \Exception("Pentru rolul 'STAFF', trebuie să specifici un location slug.");
            }
            $location = Location::where('slug', $locationSlug)->firstOrFail();
            $locationId = $location->id;
            // company_id rămâne null (derivat via relație)
        }

        $existingUser = User::where('username', $username)->first();

        if ($existingUser) {
            $this->command?->warn("Utilizatorul cu username '{$username}' există deja. Actualizez datele...");

            $existingUser->update([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role_id' => $role->id,
                'company_id' => $companyId,
                'location_id' => $locationId,
                'status' => $status,
                'email_verified_at' => $email ? now() : null,
            ]);

            $this->command?->info("Utilizator actualizat: {$username}");

            return $existingUser;
        }

        $user = User::create([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password),
            'role_id' => $role->id,
            'company_id' => $companyId,
            'location_id' => $locationId,
            'status' => $status,
            'email_verified_at' => $email ? now() : null,
        ]);

        $this->command?->info("Utilizator creat cu succes!");
        $this->command?->info("Username: {$username}");
        $this->command?->info("Nume: {$name}");
        if ($email) {
            $this->command?->info("Email: {$email}");
        }
        $this->command?->info("Rol: {$role->display_name}");
        $this->command?->info("Parolă: {$password}");

        return $user;
    }
}
