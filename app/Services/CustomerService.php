<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerService
{
    public function list(Company $company, array $filters = []): LengthAwarePaginator
    {
        $query = Customer::forCompany($company->id)
            ->when(isset($filters['search']), fn ($q) => $q->search($filters['search']))
            ->when(isset($filters['active']), fn ($q) => $q->where('is_active', $filters['active']))
            ->orderBy($filters['sort'] ?? 'name', $filters['direction'] ?? 'asc');

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function create(Company $company, array $data): Customer
    {
        $addresses = $data['addresses'] ?? [];
        unset($data['addresses']);

        $customer = $company->customers()->create($data);

        foreach ($addresses as $index => $addressData) {
            $addressData['company_id'] = $company->id;
            $addressData['is_default'] = $index === 0;
            $customer->addresses()->create($addressData);
        }

        return $customer->load('addresses');
    }

    public function update(Customer $customer, array $data): Customer
    {
        unset($data['addresses']);
        $customer->update($data);

        return $customer->fresh()->load('addresses');
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }

    public function addAddress(Customer $customer, array $data): CustomerAddress
    {
        $data['company_id'] = $customer->company_id;

        if ($data['is_default'] ?? false) {
            $customer->addresses()->update(['is_default' => false]);
        }

        return $customer->addresses()->create($data);
    }

    public function updateAddress(CustomerAddress $address, array $data): CustomerAddress
    {
        if ($data['is_default'] ?? false) {
            CustomerAddress::where('customer_id', $address->customer_id)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update($data);

        return $address->fresh();
    }

    public function deleteAddress(CustomerAddress $address): void
    {
        $address->delete();
    }

    public function search(Company $company, string $query): \Illuminate\Database\Eloquent\Collection
    {
        return Customer::forCompany($company->id)
            ->search($query)
            ->where('is_active', true)
            ->with('addresses')
            ->limit(10)
            ->get();
    }
}
