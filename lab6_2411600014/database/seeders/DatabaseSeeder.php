<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * NOTE: the WithoutModelEvents trait was removed on purpose. It disables
     * Eloquent events, which would stop InventoryTransactionObserver from
     * updating stock while seeding.
     */
    public function run(): void
    {
        // Login users. `role` is not mass assignable, so use forceFill().
        $users = [
            ['admin@gmail.com',   'Admin',   User::ROLE_ADMIN],
            ['manager@gmail.com', 'Manager', User::ROLE_MANAGER],
            ['staff@gmail.com',   'Staff',   User::ROLE_STAFF],
        ];

        foreach ($users as [$email, $name, $role]) {
            $user = User::firstOrNew(['email' => $email]);
            $user->forceFill([
                'name'     => $name,
                'password' => bcrypt('password123'),
                'role'     => $role,
            ])->save();
        }

        $this->call([
            ProductSeeder::class,
        ]);
    }
}
