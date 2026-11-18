<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2 website content. Certificates & awards, incubators, the home hero film and
 * page banner images are single records, so they live in the `settings` table instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Grants & work orders on the About page ("Backed by Grants. Proven in the Field.").
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->string('tag', 60);                // e.g. "Defence"
            $table->string('title', 160);
            $table->text('copy');
            $table->string('badge', 120)->nullable(); // e.g. "iDEX (MoD) grant"
            $table->string('note', 255)->nullable();  // small print under the badge
            $table->string('status', 80)->nullable(); // label on the photo, e.g. "Work order ongoing"
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('page', 40)->index();      // which website page shows it (config/thor.php → faq_pages)
            $table->string('question', 255);
            $table->text('answer');
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // Logo slider on the home page.
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);              // also the logo's alt text
            $table->string('logo');
            $table->string('url')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('milestones');
    }
};
