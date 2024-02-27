<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Category;
use App\Models\CategoryProduct;
use App\Models\Comment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();
        Comment::factory(200)->create();
        Category::factory(5)->create();
        Product::factory(25)->create();
        foreach ( Product::all() as $product){
            $cat = Category::inRandomOrder()->take(rand(1,3))->pluck('id'); 
            $product->categories()->attach($cat);
        }
    }
}
