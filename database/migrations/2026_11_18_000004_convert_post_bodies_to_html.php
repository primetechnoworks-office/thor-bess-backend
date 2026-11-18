<?php

use App\Support\PostBody;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Article bodies move from the website's original text format ("## heading", "- bullet", …)
 * to HTML edited in CKEditor. Converts existing rows; bodies that are already HTML are only re-cleaned.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('posts')->orderBy('id')->each(function ($post) {
            DB::table('posts')->where('id', $post->id)->update(['body' => PostBody::normalize($post->body)]);
        });
    }

    public function down(): void
    {
        // Not reversible: the text format can't express everything HTML can (links, images, tables).
    }
};
