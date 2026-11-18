<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category', 60)->index();
            $table->string('excerpt', 400);
            $table->longText('body');
            $table->string('image')->nullable();            // uploaded path or website-relative path (/images/...)
            $table->string('image_alt')->nullable();
            $table->string('author', 80)->default('"ThOR" BESS Team');
            $table->string('status', 20)->default('draft')->index(); // draft | published
            $table->boolean('is_featured')->default(false);
            $table->date('published_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
