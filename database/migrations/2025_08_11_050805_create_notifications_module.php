<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->string('subject');
            $t->text('body_markdown');
            $t->json('translations')->nullable();
            $t->timestamps();
        });

        Schema::create('email_campaigns', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->foreignId('template_id')->nullable()->constrained('email_templates')->nullOnDelete();
            $t->enum('status', ['draft','scheduled','sending','sent','failed'])->default('draft');
            $t->timestamp('scheduled_at')->nullable();
            $t->timestamps();
        });

        Schema::create('email_campaign_recipients', function (Blueprint $t) {
            $t->id();
            $t->foreignId('campaign_id')->constrained('email_campaigns')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained();
            $t->enum('status', ['pending','sent','failed'])->default('pending');
            $t->timestamp('sent_at')->nullable();
            $t->timestamps();
        });

        Schema::create('user_notification_prefs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->boolean('system')->default(true);
            $t->boolean('course')->default(true);
            $t->boolean('reminders')->default(true);
            $t->boolean('marketing')->default(false);
            $t->timestamps();
        });

        Schema::create('notification_reads', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('event');
            $t->timestamp('read_at')->nullable();
            $t->timestamps();
        });

        Schema::create('conversations', function (Blueprint $t) {
            $t->id();
            $t->string('subject');
            $t->string('context_type')->nullable();
            $t->unsignedBigInteger('context_id')->nullable();
            $t->timestamp('last_message_at')->nullable();
            $t->foreignId('created_by')->constrained('users');
            $t->timestamps();
        });

        Schema::create('conversation_participants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('role')->default('student');
            $t->boolean('can_post')->default(true);
            $t->boolean('subscribed')->default(true);
            $t->timestamps();
        });

        Schema::create('conversation_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $t->foreignId('sender_id')->constrained('users');
            $t->text('body_text')->nullable();
            $t->longText('body_html')->nullable();
            $t->json('attachments_json')->nullable();
            $t->string('email_message_id')->nullable();
            $t->unsignedBigInteger('in_reply_to_message_id')->nullable();
            $t->string('delivered_via')->default('inapp');
            $t->timestamps();
        });

        Schema::create('conversation_message_reads', function (Blueprint $t) {
            $t->id();
            $t->foreignId('message_id')->constrained('conversation_messages')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->timestamp('read_at')->nullable();
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_message_reads');
        Schema::dropIfExists('conversation_messages');
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('notification_reads');
        Schema::dropIfExists('user_notification_prefs');
        Schema::dropIfExists('email_campaign_recipients');
        Schema::dropIfExists('email_campaigns');
        Schema::dropIfExists('email_templates');
    }
};
