<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One table for every website form (contact, datasheet, quote).
     * Shared fields are columns; form-specific answers live in `details`.
     */
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->index();            // contact | datasheet | quote
            $table->string('status', 20)->default('new')->index();
            $table->string('name', 80);
            $table->string('company', 120)->nullable();
            $table->string('email', 120)->index();
            $table->string('phone', 30);
            $table->string('subject')->nullable();          // product / segment / industry summary
            $table->text('message')->nullable();
            $table->json('details')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->text('notes')->nullable();              // internal, admin only
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
