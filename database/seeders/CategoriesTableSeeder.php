<?php

namespace Database\Seeders;

use App\Models\Category;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            ['name' => 'Food', 'type' => 'expense', 'user_id' => 1],
            ['name' => 'Salary', 'type' => 'income', 'user_id' => 1],
            ['name' => 'Utilities', 'type' => 'expense', 'user_id' => 1],
            ['name' => 'Investments', 'type' => 'income', 'user_id' => 1],
        ]);
    }
}
