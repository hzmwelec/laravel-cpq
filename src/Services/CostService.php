<?php

namespace Hzmwelec\CPQ\Services;

use Hzmwelec\CPQ\Exceptions\RuntimeException;
use Hzmwelec\CPQ\Models\Cost;
use Hzmwelec\CPQ\Models\Product;
use Hzmwelec\CPQ\Models\Version;
use Hzmwelec\CPQ\Validators\SaveCostValidator;
use Hzmwelec\CPQ\Validators\SortCostValidator;
use Illuminate\Support\Facades\DB;

class CostService
{
    /**
     * @param int $productId
     * @param array $data
     * @return \Hzmwelec\CPQ\Models\Cost
     * @throws \Hzmwelec\CPQ\Exceptions\RuntimeException
     */
    public function createCost($productId, $data)
    {
        $product = Product::find($productId);

        if (!$product) {
            throw new RuntimeException('Product not found');
        }

        $version = Version::find($product->version_id);

        if (!$version) {
            throw new RuntimeException("Product's version not found");
        }

        if (!$version->is_editable) {
            throw new RuntimeException('Product is not editable');
        }

        $validatedData = SaveCostValidator::validate($product->id, $data);

        return DB::transaction(function () use ($product, $validatedData) {
            $cost = Cost::create(array_merge($validatedData, [
                'product_id' => $product->id
            ]));

            if (isset($validatedData['rules'])) {
                $cost->rules()->createMany($validatedData['rules']);
            }

            return $cost;
        });
    }

    /**
     * @param int $costId
     * @param array $data
     * @return bool
     * @throws \Hzmwelec\CPQ\Exceptions\RuntimeException
     */
    public function updateCost($costId, $data)
    {
        $cost = Cost::find($costId);

        if (!$cost) {
            throw new RuntimeException('Cost not found');
        }

        $product = Product::find($cost->product_id);

        if (!$product) {
            throw new RuntimeException("Cost's product not found");
        }

        $version = Version::find($product->version_id);

        if (!$version) {
            throw new RuntimeException("Cost's version not found");
        }

        if (!$version->is_editable) {
            throw new RuntimeException('Cost is not editable');
        }

        $validatedData = SaveCostValidator::validate($cost->product_id, $data, $cost->id);

        return DB::transaction(function () use ($cost, $validatedData) {
            $cost->update($validatedData);

            $originalRuleIds = $cost->rules()->pluck('id');

            if (isset($validatedData['rules'])) {
                foreach ($validatedData['rules'] as $ruleData) {
                    $cost->rules()->updateOrCreate(['id' => $ruleData['id'] ?? null], $ruleData);
                }

                $newRuleIds = collect($validatedData['rules'])->pluck('id');

                $diffRuleIds = $originalRuleIds->diff($newRuleIds);
            } else {
                $diffRuleIds = $originalRuleIds;
            }

            $cost->rules()->whereIn('id', $diffRuleIds)->delete();

            return true;
        });
    }

    /**
     * @param int $costId
     * @return bool
     * @throws \Hzmwelec\CPQ\Exceptions\RuntimeException
     */
    public function deleteCost($costId)
    {
        $cost = Cost::find($costId);

        if (!$cost) {
            throw new RuntimeException('Cost not found');
        }

        $product = Product::find($cost->product_id);

        if (!$product) {
            throw new RuntimeException("Cost's product not found");
        }

        $version = Version::find($product->version_id);

        if (!$version) {
            throw new RuntimeException("Cost's version not found");
        }

        if (!$version->is_deletable) {
            throw new RuntimeException('Cost is not deletable');
        }

        return DB::transaction(function () use ($cost) {
            $cost->rules()->delete();

            return $cost->delete();
        });
    }

    /**
     * @param int $productId
     * @param array $data
     * @return bool
     * @throws \Hzmwelec\CPQ\Exceptions\RuntimeException
     */
    public function sortCosts($productId, $data)
    {
        $product = Product::find($productId);

        if (!$product) {
            throw new RuntimeException('Product not found');
        }

        $version = Version::find($product->version_id);

        if (!$version) {
            throw new RuntimeException("Product's version not found");
        }

        if (!$version->is_editable) {
            throw new RuntimeException('Product is not editable');
        }

        $validatedData = SortCostValidator::validate($data);

        $costs = Cost::where('product_id', $product->id)->get();

        return DB::transaction(function () use ($costs, $validatedData) {
            foreach ($validatedData as $costData) {
                $cost = $costs->firstWhere('id', $costData['id']);

                if ($cost) {
                    $cost->sort_order = $costData['sort_order'];

                    $cost->save();
                }
            }

            return true;
        });
    }
}
