<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name      = $this->faker->randomElement([
            'Margherita Pizza', 'Pepperoni Pizza', 'BBQ Burger', 'Classic Burger',
            'Caesar Salad', 'Chocolate Cake', 'Coca-Cola', 'Natural Juice',
            'Chicken Wrap', 'French Fries', 'Onion Rings', 'Tiramisu',
        ]);
        $basePrice = $this->faker->randomFloat(2, 8, 60);

        return [
            'company_id'        => Company::factory(),
            'category_id'       => Category::factory(),
            'name'              => $name,
            'slug'              => Str::slug($name) . '-' . $this->faker->unique()->randomNumber(4),
            'description'       => $this->faker->optional()->paragraph(),
            'image_path'        => null,
            'base_price'        => $basePrice,
            'promotional_price' => $this->faker->optional(0.3)->randomFloat(2, $basePrice * 0.7, $basePrice * 0.9),
            'sku'               => $this->faker->optional()->bothify('SKU-####'),
            'is_active'         => true,
            'preparation_time'  => $this->faker->randomElement([10, 15, 20, 25, 30]),
            'sort_order'        => $this->faker->numberBetween(0, 50),
        ];
    }
}
