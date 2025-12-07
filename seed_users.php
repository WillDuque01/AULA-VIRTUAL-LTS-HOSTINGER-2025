<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

require '/var/www/app.letstalkspanish.io/vendor/autoload.php';
$app = require '/var/www/app.letstalkspanish.io/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function ensureUser(string $email, string $name, string $password, array $roles): void {
    $user = User::updateOrCreate(
        ['email' => $email],
        [
            'name' => $name,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]
    );

    $user->syncRoles($roles);

    echo "User {$email} ready with roles: " . implode(',', $roles) . PHP_EOL;
}

ensureUser('academy@letstalkspanish.io', 'LTS Academy Admin', 'AcademyVPS2025!', ['Admin', 'teacher_admin']);
ensureUser('teacher.admin@letstalkspanish.io', 'Teacher Admin QA', 'TeacherAdmin2025!', ['teacher_admin']);
ensureUser('teacher@letstalkspanish.io', 'Teacher QA', 'TeacherQA2025!', ['teacher']);
ensureUser('student@letstalkspanish.io', 'Student QA', 'StudentQA2025!', ['student_paid']);
