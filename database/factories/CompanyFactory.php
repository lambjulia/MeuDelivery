<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'name'              => $this->faker->company(),
            'trade_name'        => $this->faker->companySuffix() . ' Delivery',
            'document'          => $this->faker->numerify('##.###.###/####-##'),
            'phone'             => $this->faker->numerify('(##) #####-####'),
            'email'             => $this->faker->companyEmail(),
            'logo_path'         => null,
            'primary_color'     => $this->faker->randomElement(['#F97316', '#3B82F6', '#10B981', '#8B5CF6', '#EF4444']),
            'zip_code'          => $this->faker->numerify('#####-###'),
            'street'            => $this->faker->streetName(),
            'number'            => $this->faker->buildingNumber(),
            'complement'        => null,
            'district'          => $this->faker->citySuffix(),
            'city'              => $this->faker->city(),
            'state'             => $this->faker->stateAbbr(),
            'business_hours'    => [
                'monday'    => ['open' => '08:00', 'close' => '22:00', 'active' => true],
                'tuesday'   => ['open' => '08:00', 'close' => '22:00', 'active' => true],
                'wednesday' => ['open' => '08:00', 'close' => '22:00', 'active' => true],
                'thursday'  => ['open' => '08:00', 'close' => '22:00', 'active' => true],
                'friday'    => ['open' => '08:00', 'close' => '23:00', 'active' => true],
                'saturday'  => ['open' => '10:00', 'close' => '23:00', 'active' => true],
                'sunday'    => ['open' => '10:00', 'close' => '22:00', 'active' => true],
            ],
            'default_delivery_fee'     => $this->faker->randomFloat(2, 3, 12),
            'accepted_payment_methods' => ['cash', 'credit_card', 'debit_card', 'pix'],
            'default_locale'           => 'pt_BR',
            'is_active'                => true,
        ];
    }
}
