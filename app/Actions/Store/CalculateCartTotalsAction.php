<?php

namespace App\Actions\Store;

use App\Models\ProductOption;
use App\Models\ProductOptionGroup;

class CalculateCartTotalsAction
{
    /**
     * Re-calculate totals server-side from the items array received from the client.
     * Each item: { product_id, quantity, options: [{ option_id }], notes }
     * Returns: { items (enriched), subtotal }
     */
    public function execute(array $items, int $companyId): array
    {
        $subtotal = 0;
        $enriched = [];

        foreach ($items as $item) {
            $product = \App\Models\Product::where('id', $item['product_id'])
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->with(['optionGroups.activeOptions'])
                ->first();

            if (! $product) {
                throw new \InvalidArgumentException("Product #{$item['product_id']} not found or unavailable.");
            }

            $unitPrice   = (float) ($product->promotional_price ?? $product->base_price);
            $optionTotal = 0;
            $optionsData = [];

            foreach ($item['options'] ?? [] as $selectedOption) {
                $option = ProductOption::where('id', $selectedOption['option_id'])
                    ->where('is_active', true)
                    ->with('optionGroup')
                    ->first();

                if (! $option) {
                    continue;
                }

                // Ensure the option belongs to this product
                if ($option->optionGroup->product_id !== $product->id) {
                    throw new \InvalidArgumentException("Option #{$option->id} does not belong to product #{$product->id}.");
                }

                $optionTotal += (float) $option->additional_price;
                $optionsData[] = [
                    'product_option_id'          => $option->id,
                    'product_option_name'        => $option->name,
                    'product_option_group_name'  => $option->optionGroup->name,
                    'additional_price'           => (float) $option->additional_price,
                ];
            }

            // Validate required groups
            foreach ($product->optionGroups->where('is_active', true)->where('is_required', true) as $group) {
                $selectedIds = array_column($optionsData, 'product_option_id');
                $groupOptionIds = $group->activeOptions->pluck('id')->toArray();
                $intersection = array_intersect($selectedIds, $groupOptionIds);

                if (count($intersection) < $group->min_selections) {
                    throw new \InvalidArgumentException(
                        "Group \"{$group->name}\" requires at least {$group->min_selections} selection(s) for \"{$product->name}\"."
                    );
                }
            }

            $itemTotal  = ($unitPrice + $optionTotal) * $item['quantity'];
            $subtotal  += $itemTotal;

            $enriched[] = [
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'unit_price'   => $unitPrice + $optionTotal,
                'quantity'     => (int) $item['quantity'],
                'notes'        => $item['notes'] ?? null,
                'total'        => $itemTotal,
                'options'      => $optionsData,
            ];
        }

        return ['items' => $enriched, 'subtotal' => $subtotal];
    }
}
