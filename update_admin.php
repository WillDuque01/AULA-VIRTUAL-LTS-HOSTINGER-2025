<?php
require '/var/www/app.letstalkspanish.io/vendor/autoload.php';
$app = require '/var/www/app.letstalkspanish.io/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$user = App\Models\User::where('email', 'academy@letstalkspanish.io')->first();
if (! $user) {
    fwrite(STDERR, "Usuario no encontrado\n");
    exit(1);
}
$user->password = Illuminate\Support\Facades\Hash::make('AcademyVPS2025!');
$user->save();
fwrite(STDOUT, "Password actualizada para {$user->email}\n");
