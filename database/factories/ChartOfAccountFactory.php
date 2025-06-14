<?php

namespace Database\Factories;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChartOfAccount>
 */
class ChartOfAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->numberBetween(1, 1000), // Generates a unique integer for the code
            'name' => $this->faker->word, // Generates a random word for the name
            'category_name' =>  $this->faker->word, // Create a new category and use its name
            'user_id' =>  $this->faker->numberBetween(1, 1000), // Creates a new user and use its ID
        ];
    }
}
