<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class InitialSetupSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            TierSeeder::class,
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@lms.test'],
            ['name' => 'Admin', 'password' => bcrypt('admin1234')]
        );

        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $this->call(DemoCourseSeeder::class);
        $this->call(DemoQuizSeeder::class);
    }
}
