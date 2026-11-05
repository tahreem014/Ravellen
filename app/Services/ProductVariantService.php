<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

/**
 * The variant rows of the admin Add / Edit product form, saved together with the rest of the
 * product by the form's one Save button: existing variants edited in place (variants[id][…]),
 * removed ones (remove_variants[]), and filled-in "new variant" rows (new_variants[n][…]).
 */
class ProductVariantService
{
    private const ROW_FIELDS  = ['variant_name', 'price', 'sale_price', 'min_qty', 'max_qty', 'stock', 'status'];
    private const PACK_FIELDS = ['stock', 'status'];

    /** Validation rules for the submitted rows; a Mystery Balls pack only has its stock and status edited here. */
    public function rules(bool $packProduct): array
    {
        $existing = $packProduct
            ? ['variants.*.stock' => ['required', 'integer', 'min:0'], 'variants.*.status' => ['required', Rule::in(ProductVariant::STATUSES)]]
            : $this->rowRules('variants.*');

        return [
            'variants'        => ['nullable', 'array'],
            'new_variants'    => ['nullable', 'array'],
            'remove_variants' => ['nullable', 'array'],
            'remove_variants.*' => ['integer'],
        ] + $existing + $this->rowRules('new_variants.*');
    }

    public function messages(): array
    {
        return [
            'variants.*.sale_price.lt'     => 'The sale price must be lower than the regular price.',
            'new_variants.*.sale_price.lt' => 'The sale price must be lower than the regular price.',
        ];
    }

    public function attributes(): array
    {
        $names = [
            'variant_name' => 'variant name', 'price' => 'variant price', 'sale_price' => 'variant sale price',
            'min_qty' => 'variant minimum quantity', 'max_qty' => 'variant maximum quantity',
            'stock' => 'variant stock', 'status' => 'variant status',
        ];

        $attributes = [];
        foreach (['variants.*.', 'new_variants.*.'] as $prefix) {
            foreach ($names as $field => $name) {
                $attributes[$prefix . $field] = ($prefix === 'new_variants.*.' ? 'new ' : '') . $name;
            }
        }

        return $attributes;
    }

    /**
     * Tidy the submitted rows before they are validated: only this product's own variants count,
     * those marked for removal leave validation (and stay marked, in remove_variants, if the form
     * comes back with errors), and "new variant" rows left blank are dropped. $newAllowed is false
     * for Mystery Balls products, whose packs come from the Pricing section.
     */
    public function prepare(Request $request, ?Product $product, bool $newAllowed): void
    {
        $own   = $product?->variants->pluck('id')->map(fn ($id) => (int) $id)->all() ?? [];
        $rows  = collect($request->input('variants', []))
            ->filter(fn ($row, $id) => is_array($row) && in_array((int) $id, $own, true));

        // a pack can't be removed here: its quantity is taken off in the Pricing section
        $remove = $product?->usesPackPricing()
            ? []
            : collect($request->input('remove_variants', []))->map(fn ($id) => (int) $id)->filter(fn ($id) => in_array($id, $own, true))->unique()->values()->all();

        $new = $newAllowed
            ? collect($request->input('new_variants', []))
                ->filter(fn ($row) => is_array($row) && (filled($row['variant_name'] ?? null) || filled($row['price'] ?? null)))
                ->values()->all()
            : [];

        $request->merge([
            'variants'        => $rows->reject(fn ($row, $id) => in_array((int) $id, $remove, true))->all(),
            'remove_variants' => $remove,
            'new_variants'    => $new,
        ]);
    }

    /**
     * Save the validated rows: edit the existing variants, remove the marked ones, add the new ones.
     * Run before the pricing step, so a pack the Pricing section then takes off sale stays off.
     *
     * @return int how many variants were added
     */
    public function apply(Product $product, array $data): int
    {
        $variants = $product->variants()->get()->keyBy('id');
        $fields   = $product->usesPackPricing() ? self::PACK_FIELDS : self::ROW_FIELDS;

        foreach ($data['remove_variants'] ?? [] as $id) {
            $variants->get((int) $id)?->delete();
        }

        foreach ($data['variants'] ?? [] as $id => $row) {
            $variants->get((int) $id)?->update(Arr::only($row, $fields));
        }

        foreach ($data['new_variants'] ?? [] as $row) {
            $product->variants()->create(Arr::only($row, self::ROW_FIELDS));
        }

        return count($data['new_variants'] ?? []);
    }

    private function rowRules(string $prefix): array
    {
        return [
            "$prefix.variant_name" => ['required', 'string', 'max:255'],
            "$prefix.price"        => ['required', 'numeric', 'min:0'],
            "$prefix.sale_price"   => ['nullable', 'numeric', 'gt:0', "lt:$prefix.price"],
            "$prefix.min_qty"      => ['required', 'integer', 'min:1'],
            "$prefix.max_qty"      => ['required', 'integer', "gte:$prefix.min_qty"],
            "$prefix.stock"        => ['required', 'integer', 'min:0'],
            "$prefix.status"       => ['required', Rule::in(ProductVariant::STATUSES)],
        ];
    }
}
