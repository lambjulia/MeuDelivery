<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name'       => $this->faker->name(),
            'phone'      => $this->faker->numerify('(##) #####-####'),
            'email'      => $this->faker->optional()->safeEmail(),
            'document'   => null,
            'notes'      => $this->faker->optional()->sentence(),
            'is_active'  => true,
        ];
    }
}
