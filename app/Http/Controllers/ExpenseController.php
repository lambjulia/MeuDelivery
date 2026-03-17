<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    public function index(Request $request): Response
    {
        $expenses = Expense::forCompany($request->user()->company_id)
            ->when($request->date_from, fn ($q) => $q->where('occurred_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->where('occurred_at', '<=', $request->date_to))
            ->when($request->category, fn ($q) => $q->where('category', $request->category))
            ->orderBy('occurred_at', 'desc')
            ->paginate(20);

        $categories = Expense::forCompany($request->user()->company_id)
            ->distinct()->pluck('category');

        return Inertia::render('Expenses/Index', [
            'expenses'   => $expenses,
            'categories' => $categories,
            'filters'    => $request->only(['date_from', 'date_to', 'category']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category'    => ['required', 'string', 'max:100'],
            'amount'      => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:500'],
            'occurred_at' => ['required', 'date'],
        ]);

        $data['company_id'] = $request->user()->company_id;
        Expense::create($data);

        return back()->with('success', 'Expense recorded.');
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        abort_if($expense->company_id !== $request->user()->company_id, 403);

        $data = $request->validate([
            'category'    => ['string', 'max:100'],
            'amount'      => ['numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:500'],
            'occurred_at' => ['date'],
        ]);

        $expense->update($data);

        return back()->with('success', 'Expense updated.');
    }

    public function destroy(Request $request, Expense $expense): RedirectResponse
    {
        abort_if($expense->company_id !== $request->user()->company_id, 403);

        $expense->delete();

        return back()->with('success', 'Expense deleted.');
    }
}
