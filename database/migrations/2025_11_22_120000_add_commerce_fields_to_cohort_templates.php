<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cohort_templates', function (Blueprint $table): void {
            $table->decimal('price_amount', 10, 2)->default(0)->after('capacity');
            $table->string('price_currency', 3)->default('USD')->after('price_amount');
            $table->string('status', 32)->default('draft')->after('price_currency');
            $table->boolean('is_featured')->default(false)->after('status');
        });

        Schema::create('cohort_registrations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cohort_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 32)->default('confirmed');
            $table->string('payment_reference')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['cohort_template_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cohort_registrations');

        Schema::table('cohort_templates', function (Blueprint $table): void {
            $table->dropColumn([
                'price_amount',
                'price_currency',
                'status',
                'is_featured',
            ]);
        });
    }
};


