<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Company;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\DeliveryDriver;
use App\Models\DeliveryZone;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionGroup;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoCompanySeeder extends Seeder
{
    public function run(): void
    {
        // Create demo company
        $company = Company::create([
            'name'              => 'Burger House',
            'trade_name'        => 'Burger House Delivery',
            'slug'              => 'burger-house',
            'document'          => '12.345.678/0001-99',
            'phone'             => '(11) 98765-4321',
            'email'             => 'contato@burgerhouse.com',
            'primary_color'     => '#F97316',
            'zip_code'          => '01310-100',
            'street'            => 'Avenida Paulista',
            'number'            => '1000',
            'district'          => 'Bela Vista',
            'city'              => 'São Paulo',
            'state'             => 'SP',
            'business_hours'    => [
                'monday'    => ['open' => '11:00', 'close' => '23:00', 'active' => true],
                'tuesday'   => ['open' => '11:00', 'close' => '23:00', 'active' => true],
                'wednesday' => ['open' => '11:00', 'close' => '23:00', 'active' => true],
                'thursday'  => ['open' => '11:00', 'close' => '23:00', 'active' => true],
                'friday'    => ['open' => '11:00', 'close' => '00:00', 'active' => true],
                'saturday'  => ['open' => '12:00', 'close' => '00:00', 'active' => true],
                'sunday'    => ['open' => '12:00', 'close' => '22:00', 'active' => true],
            ],
            'default_delivery_fee'     => 5.00,
            'accepted_payment_methods' => ['cash', 'credit_card', 'debit_card', 'pix'],
            'default_locale'           => 'pt_BR',
            'is_active'                => true,
        ]);

        // Create owner user
        $owner = User::create([
            'company_id'        => $company->id,
            'name'              => 'Admin User',
            'email'             => 'admin@meudelivery.test',
            'password'          => Hash::make('password'),
            'role'              => UserRole::Owner->value,
            'phone'             => '(11) 99999-0000',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        // Create additional users
        User::factory()->forCompany($company)->manager()->create([
            'name'  => 'Manager User',
            'email' => 'manager@meudelivery.test',
        ]);

        User::factory()->forCompany($company)->create([
            'name'  => 'Attendant User',
            'email' => 'attendant@meudelivery.test',
        ]);

        // Categories
        $categories = [
            ['name' => 'Burgers',     'sort_order' => 1],
            ['name' => 'Combos',      'sort_order' => 2],
            ['name' => 'Sides',       'sort_order' => 3],
            ['name' => 'Drinks',      'sort_order' => 4],
            ['name' => 'Desserts',    'sort_order' => 5],
        ];

        $createdCategories = [];
        foreach ($categories as $catData) {
            $createdCategories[$catData['name']] = Category::create(array_merge($catData, [
                'company_id' => $company->id,
                'is_active'  => true,
            ]));
        }

        // Products with options
        $burger = Product::create([
            'company_id'       => $company->id,
            'category_id'      => $createdCategories['Burgers']->id,
            'name'             => 'Classic Burger',
            'description'      => 'Juicy beef patty with lettuce, tomato, and special sauce',
            'base_price'       => 22.90,
            'is_active'        => true,
            'preparation_time' => 20,
            'sort_order'       => 1,
        ]);

        // Burger size group
        $sizeGroup = ProductOptionGroup::create([
            'product_id'     => $burger->id,
            'name'           => 'Size',
            'is_required'    => true,
            'min_selections' => 1,
            'max_selections' => 1,
            'is_multiple'    => false,
            'sort_order'     => 1,
            'is_active'      => true,
        ]);
        ProductOption::create(['product_option_group_id' => $sizeGroup->id, 'name' => 'Small',  'additional_price' => 0,    'sort_order' => 1]);
        ProductOption::create(['product_option_group_id' => $sizeGroup->id, 'name' => 'Medium', 'additional_price' => 5.00, 'sort_order' => 2]);
        ProductOption::create(['product_option_group_id' => $sizeGroup->id, 'name' => 'Large',  'additional_price' => 9.00, 'sort_order' => 3]);

        // Extras group
        $extrasGroup = ProductOptionGroup::create([
            'product_id'     => $burger->id,
            'name'           => 'Extras',
            'is_required'    => false,
            'min_selections' => 0,
            'max_selections' => 5,
            'is_multiple'    => true,
            'sort_order'     => 2,
            'is_active'      => true,
        ]);
        ProductOption::create(['product_option_group_id' => $extrasGroup->id, 'name' => 'Bacon',          'additional_price' => 4.00, 'sort_order' => 1]);
        ProductOption::create(['product_option_group_id' => $extrasGroup->id, 'name' => 'Extra Cheese',   'additional_price' => 3.00, 'sort_order' => 2]);
        ProductOption::create(['product_option_group_id' => $extrasGroup->id, 'name' => 'Fried Egg',      'additional_price' => 3.50, 'sort_order' => 3]);
        ProductOption::create(['product_option_group_id' => $extrasGroup->id, 'name' => 'Caramelized Onion', 'additional_price' => 2.00, 'sort_order' => 4]);

        Product::create(['company_id' => $company->id, 'category_id' => $createdCategories['Burgers']->id,
            'name' => 'BBQ Burger', 'description' => 'Smoky BBQ burger with crispy onion rings', 'base_price' => 27.90, 'is_active' => true, 'preparation_time' => 20, 'sort_order' => 2]);
        Product::create(['company_id' => $company->id, 'category_id' => $createdCategories['Burgers']->id,
            'name' => 'Veggie Burger', 'description' => 'Plant-based patty with fresh vegetables', 'base_price' => 24.90, 'promotional_price' => 21.90, 'is_active' => true, 'preparation_time' => 15, 'sort_order' => 3]);
        Product::create(['company_id' => $company->id, 'category_id' => $createdCategories['Sides']->id,
            'name' => 'French Fries', 'description' => 'Crispy golden fries', 'base_price' => 12.90, 'is_active' => true, 'preparation_time' => 10, 'sort_order' => 1]);
        Product::create(['company_id' => $company->id, 'category_id' => $createdCategories['Sides']->id,
            'name' => 'Onion Rings', 'description' => 'Beer-battered onion rings', 'base_price' => 14.90, 'is_active' => true, 'preparation_time' => 12, 'sort_order' => 2]);
        Product::create(['company_id' => $company->id, 'category_id' => $createdCategories['Drinks']->id,
            'name' => 'Coca-Cola 350ml', 'description' => 'Ice cold Coke', 'base_price' => 6.00, 'is_active' => true, 'sort_order' => 1]);
        Product::create(['company_id' => $company->id, 'category_id' => $createdCategories['Drinks']->id,
            'name' => 'Natural Juice', 'description' => 'Fresh squeezed juice', 'base_price' => 9.90, 'is_active' => true, 'sort_order' => 2]);
        Product::create(['company_id' => $company->id, 'category_id' => $createdCategories['Desserts']->id,
            'name' => 'Chocolate Brownie', 'description' => 'Warm chocolate brownie with ice cream', 'base_price' => 16.90, 'is_active' => true, 'preparation_time' => 5, 'sort_order' => 1]);

        // Delivery zones
        DeliveryZone::create(['company_id' => $company->id, 'name' => 'Central Zone',  'neighborhoods' => ['Centro', 'Bela Vista', 'Consolação'], 'delivery_fee' => 5.00,  'estimated_time' => 30, 'is_active' => true]);
        DeliveryZone::create(['company_id' => $company->id, 'name' => 'Expanded Zone', 'neighborhoods' => ['Pinheiros', 'Vila Madalena', 'Perdizes'], 'delivery_fee' => 8.00,  'estimated_time' => 45, 'is_active' => true]);
        DeliveryZone::create(['company_id' => $company->id, 'name' => 'Extended Zone', 'neighborhoods' => ['Moema', 'Ibirapuera', 'Vila Clementino'], 'delivery_fee' => 12.00, 'estimated_time' => 60, 'is_active' => true]);

        // Delivery drivers
        DeliveryDriver::create(['company_id' => $company->id, 'name' => 'Carlos Moto',    'phone' => '(11) 91111-1111', 'vehicle_type' => 'motorcycle', 'status' => 'available', 'is_active' => true]);
        DeliveryDriver::create(['company_id' => $company->id, 'name' => 'Ana Bicicleta',  'phone' => '(11) 92222-2222', 'vehicle_type' => 'bike',       'status' => 'offline',   'is_active' => true]);
        DeliveryDriver::create(['company_id' => $company->id, 'name' => 'Pedro Carro',    'phone' => '(11) 93333-3333', 'vehicle_type' => 'car',        'status' => 'available', 'is_active' => true]);

        // Customers with addresses
        $customers = [
            ['name' => 'Maria Silva',    'phone' => '(11) 91234-5678', 'email' => 'maria@test.com'],
            ['name' => 'João Santos',    'phone' => '(11) 92345-6789', 'email' => 'joao@test.com'],
            ['name' => 'Ana Oliveira',   'phone' => '(11) 93456-7890', 'email' => null],
            ['name' => 'Carlos Lima',    'phone' => '(11) 94567-8901', 'email' => 'carlos@test.com'],
            ['name' => 'Laura Ferreira', 'phone' => '(11) 95678-9012', 'email' => null],
        ];
        foreach ($customers as $cd) {
            $customer = Customer::create(array_merge($cd, ['company_id' => $company->id, 'is_active' => true]));
            CustomerAddress::create([
                'customer_id' => $customer->id,
                'company_id'  => $company->id,
                'street'      => 'Rua das Flores',
                'number'      => (string) rand(1, 999),
                'district'    => 'Centro',
                'city'        => 'São Paulo',
                'state'       => 'SP',
                'zip_code'    => '01310-100',
                'is_default'  => true,
            ]);
        }

        // Coupons
        Coupon::create(['company_id' => $company->id, 'code' => 'WELCOME10', 'type' => 'percentage', 'value' => 10, 'min_order_amount' => 30, 'max_uses' => 100, 'is_active' => true]);
        Coupon::create(['company_id' => $company->id, 'code' => 'FRETE0',    'type' => 'fixed',      'value' => 5,  'min_order_amount' => 25, 'max_uses' => 50,  'is_active' => true]);
        Coupon::create(['company_id' => $company->id, 'code' => 'DESCONTO5', 'type' => 'fixed',      'value' => 5,  'min_order_amount' => 40, 'max_uses' => null,'is_active' => true, 'expires_at' => now()->addMonths(3)]);
    }
}
