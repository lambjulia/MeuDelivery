<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CompanyService
{
    public function updateSettings(Company $company, array $data): Company
    {
        if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $data['logo_path'] = $data['logo']->store('companies/logos', 'public');
            unset($data['logo']);
        }

        $company->update($data);

        return $company->fresh();
    }

    public function getSettings(User $user): Company
    {
        return $user->company;
    }
}
