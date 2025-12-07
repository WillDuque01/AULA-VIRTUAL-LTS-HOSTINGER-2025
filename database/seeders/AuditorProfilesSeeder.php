<?php

namespace Database\Seeders;

use App\Models\CohortTemplate;
use App\Models\PracticePackage;
use App\Models\PracticePackageOrder;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuditorProfilesSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = 'AuditorQA2025!';

    public function run(): void
    {
        $this->call(RolesSeeder::class);

        DB::transaction(function (): void {
            $admin = $this->createUser([
                'name' => 'Admin Principal QA',
                'email' => 'academy@letstalkspanish.io',
            ], ['admin', 'teacher_admin']);

            $teacherAdmin = $this->createUser([
                'name' => 'Teacher Admin QA',
                'email' => 'teacher.admin.qa@letstalkspanish.io',
            ], ['teacher_admin']);

            $studentPaid = $this->createUser([
                'name' => 'Student Paid QA',
                'email' => 'student.paid@letstalkspanish.io',
            ], ['student_paid']);

            $studentPending = $this->createUser([
                'name' => 'Student Pending QA',
                'email' => 'student.pending@letstalkspanish.io',
            ], ['student_free']);

            $studentWaitlist = $this->createUser([
                'name' => 'Student Waitlist QA',
                'email' => 'student.waitlist@letstalkspanish.io',
            ], ['student_free']);

            $package = $this->ensurePracticePackage($admin->id);

            $this->ensureOrder($package, $studentPaid, 'paid', [
                'sessions_remaining' => $package->sessions_count,
                'paid_at' => Carbon::now(),
            ]);

            $this->ensureOrder($package, $studentPending, 'pending');

            $this->ensureCohortSoldOut($studentWaitlist, $teacherAdmin->id);
        });
    }

    private function createUser(array $data, array $roles): User
    {
        $user = User::updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make(self::DEFAULT_PASSWORD),
            ]
        );

        $user->syncRoles($roles);

        return $user;
    }

    private function ensurePracticePackage(int $creatorId): PracticePackage
    {
        return PracticePackage::updateOrCreate(
            ['title' => 'QA Discord Pack'],
            [
                'creator_id' => $creatorId,
                'sessions_count' => 5,
                'price_amount' => 49.99,
                'price_currency' => 'USD',
                'is_global' => true,
                'visibility' => 'public',
                'delivery_platform' => 'discord',
                'delivery_url' => 'https://discord.gg/letstalkspanish',
                'status' => 'published',
            ]
        );
    }

    private function ensureOrder(PracticePackage $package, User $user, string $status, array $overrides = []): void
    {
        PracticePackageOrder::updateOrCreate(
            [
                'practice_package_id' => $package->id,
                'user_id' => $user->id,
            ],
            array_merge([
                'status' => $status,
                'sessions_remaining' => $status === 'paid'
                    ? $package->sessions_count
                    : 0,
                'payment_reference' => Str::uuid()->toString(),
                'paid_at' => $status === 'paid' ? Carbon::now() : null,
                'meta' => [
                    'seeded' => true,
                    'source' => 'AuditorProfilesSeeder',
                ],
            ], $overrides)
        );
    }

    private function ensureCohortSoldOut(User $waitlistUser, int $creatorId): void
    {
        CohortTemplate::updateOrCreate(
            ['slug' => 'qa-full-cohort'],
            [
                'name' => 'QA Cohort Sold Out',
                'description' => 'Plantilla usada para pruebas de cupos agotados.',
                'type' => 'cohort',
                'cohort_label' => 'QA',
                'duration_minutes' => 60,
                'capacity' => 1,
                'enrolled_count' => 1,
                'requires_package' => false,
                'status' => 'published',
                'price_amount' => 29.99,
                'price_currency' => 'USD',
                'is_featured' => false,
                'slots' => [
                    [
                        'day' => 'monday',
                        'time' => '10:00',
                    ],
                ],
                'meta' => [
                    'seeded' => true,
                    'waitlist_user' => $waitlistUser->email,
                ],
                'created_by' => $creatorId,
            ]
        );
    }
}

