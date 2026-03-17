<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = $this->faker->randomElement([
            'Pizzas', 'Burgers', 'Drinks', 'Desserts', 'Salads',
            'Pasta', 'Sushi', 'Sandwiches', 'Fried Foods', 'Combos',
        ]);

        return [
            'company_id'  => Company::factory(),
            'name'        => $name,
            'slug'        => Str::slug($name) . '-' . $this->faker->unique()->randomNumber(4),
            'description' => $this->faker->optional()->sentence(),
            'image_path'  => null,
            'sort_order'  => $this->faker->numberBetween(0, 20),
            'is_active'   => true,
        ];
    }
}
