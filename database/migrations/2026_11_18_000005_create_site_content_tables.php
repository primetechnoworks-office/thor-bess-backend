<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Phase 1 website content managed from the admin panel. */
return new class extends Migration
{
    public function up(): void
    {
        // LFP battery catalogue (website: /products/lfp-battery).
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();                 // web address + datasheet form value; fixed after creation
            $table->string('name');
            $table->string('series', 10)->index();            // 12V | 24V | 48V
            $table->unsignedSmallInteger('voltage');
            $table->string('capacity_label', 40);             // e.g. "100 Ah" or "15 / 30 Ah"
            $table->json('variants');                         // [{label, capacity_ah, energy, price}]
            $table->unsignedInteger('cycles');
            $table->unsignedTinyInteger('service_life');      // years
            $table->unsignedTinyInteger('guarantee');         // years
            $table->unsignedTinyInteger('warranty');          // years
            $table->string('model', 80)->nullable();
            $table->text('summary');
            $table->json('best_for');                         // list of short phrases
            $table->json('images');                           // ordered paths; first = cover
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->text('quote');
            $table->string('name', 80);
            $table->string('role', 120)->nullable();          // "CEO, Company"
            $table->string('avatar')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('role', 120);
            $table->string('image')->nullable();
            $table->string('linkedin')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // Company details shown across the website (phone, emails, offices, social links…).
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->json('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('products');
    }
};
