<?php

namespace App\Http\Controllers;

use App\Services\CompanyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanySettingsController extends Controller
{
    public function __construct(private readonly CompanyService $companyService)
    {
    }

    public function show(Request $request): Response
    {
        return Inertia::render('Settings/Company', [
            'company' => $request->user()->company,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_if(! $user->canManage(), 403);

        $data = $request->validate([
            'name'                     => ['required', 'string', 'max:150'],
            'trade_name'               => ['nullable', 'string', 'max:150'],
            'document'                 => ['nullable', 'string', 'max:20'],
            'phone'                    => ['nullable', 'string', 'max:20'],
            'email'                    => ['nullable', 'email', 'max:150'],
            'logo'                     => ['nullable', 'image', 'max:2048'],
            'primary_color'            => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'zip_code'                 => ['nullable', 'string', 'max:10'],
            'street'                   => ['nullable', 'string', 'max:200'],
            'number'                   => ['nullable', 'string', 'max:20'],
            'complement'               => ['nullable', 'string', 'max:100'],
            'district'                 => ['nullable', 'string', 'max:100'],
            'city'                     => ['nullable', 'string', 'max:100'],
            'state'                    => ['nullable', 'string', 'size:2'],
            'business_hours'           => ['nullable', 'array'],
            'default_delivery_fee'     => ['nullable', 'numeric', 'min:0'],
            'accepted_payment_methods' => ['nullable', 'array'],
            'default_locale'           => ['nullable', 'in:pt_BR,en,es'],
        ]);

        $this->companyService->updateSettings($user->company, $data);

        return back()->with('success', 'Company settings updated.');
    }
}
