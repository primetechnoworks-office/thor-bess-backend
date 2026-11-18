<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

/**
 * Imports the articles currently hard-coded in the website (frontend/app/data/blog.ts),
 * exported to database/seeders/data/posts.json. Existing slugs are left untouched.
 */
class PostSeeder extends Seeder
{
    public function run(): void
    {
        $rows = json_decode(file_get_contents(database_path('seeders/data/posts.json')), true) ?? [];

        foreach ($rows as $row) {
            Post::firstOrCreate(['slug' => $row['slug']], [
                'title' => $row['title'],
                'category' => $row['category'],
                'excerpt' => $row['excerpt'],
                'body' => $row['body'],
                'image' => $row['image'],
                'image_alt' => $row['alt'],
                'author' => $row['author'],
                'status' => Post::STATUS_PUBLISHED,
                'is_featured' => $row['featured'],
                'published_at' => $row['date'],
            ]);
        }
    }
}
