<?php

namespace Hzmwelec\CPQ\Services;

use Hzmwelec\CPQ\Exceptions\RuntimeException;
use Hzmwelec\CPQ\Models\Factor;
use Hzmwelec\CPQ\Models\Product;
use Hzmwelec\CPQ\Models\Version;
use Hzmwelec\CPQ\Validators\SaveFactorValidator;
use Hzmwelec\CPQ\Validators\SortFactorValidator;
use Illuminate\Support\Facades\DB;

class FactorService
{
    /**
     * @param int $productId
     * @param array $data
     * @return \Hzmwelec\CPQ\Models\Factor
     * @throws \Hzmwelec\CPQ\Exceptions\RuntimeException
     */
    public function createFactor($productId, $data)
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

        $validatedData = SaveFactorValidator::validate($product->id, $data);

        return DB::transaction(function () use ($product, $validatedData) {
            $factor = Factor::create(array_merge($validatedData, [
                'product_id' => $product->id
            ]));

            if ($validatedData['is_optional'] && isset($validatedData['options'])) {
                $factor->options()->createMany($validatedData['options']);
            }

            return $factor;
        });
    }

    /**
     * @param int $factorId
     * @param array $data
     * @return bool
     * @throws \Hzmwelec\CPQ\Exceptions\RuntimeException
     */
    public function updateFactor($factorId, $data)
    {
        $factor = Factor::find($factorId);

        if (!$factor) {
            throw new RuntimeException('Factor not found');
        }

        $product = Product::find($factor->product_id);

        if (!$product) {
            throw new RuntimeException("Factor's product not found");
        }

        $version = Version::find($product->version_id);

        if (!$version) {
            throw new RuntimeException("Factor's version not found");
        }

        if (!$version->is_editable) {
            throw new RuntimeException('Factor is not editable');
        }

        $validatedData = SaveFactorValidator::validate($factor->product_id, $data, $factor->id);

        return DB::transaction(function () use ($factor, $validatedData) {
            $factor->update($validatedData);

            $originalOptionIds = $factor->options()->pluck('id');

            if ($factor->is_optional && isset($validatedData['options'])) {
                foreach ($validatedData['options'] as $optionData) {
                    $factor->options()->updateOrCreate(['id' => $optionData['id'] ?? null], $optionData);
                }

                $newOptionIds = collect($validatedData['options'])->pluck('id');

                $diffOptionIds = $originalOptionIds->diff($newOptionIds);
            } else {
                $diffOptionIds = $originalOptionIds;
            }

            $factor->options()->whereIn('id', $diffOptionIds)->delete();

            return true;
        });
    }

    /**
     * @param int $factorId
     * @return bool
     * @throws \Hzmwelec\CPQ\Exceptions\RuntimeException
     */
    public function deleteFactor($factorId)
    {
        $factor = Factor::find($factorId);

        if (!$factor) {
            throw new RuntimeException('Factor not found');
        }

        $product = Product::find($factor->product_id);

        if (!$product) {
            throw new RuntimeException("Factor's product not found");
        }

        $version = Version::find($product->version_id);

        if (!$version) {
            throw new RuntimeException("Factor's version not found");
        }

        if (!$version->is_deletable) {
            throw new RuntimeException('Factor is not deletable');
        }

        return DB::transaction(function () use ($factor) {
            $factor->options()->delete();

            return $factor->delete();
        });
    }

    /**
     * @param int $productId
     * @param array $data
     * @return bool
     * @throws \Hzmwelec\CPQ\Exceptions\RuntimeException
     */
    public function sortFactors($productId, $data)
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

        $validatedData = SortFactorValidator::validate($data);

        $factors = Factor::with('options')->where('product_id', $product->id)->get();

        return DB::transaction(function () use ($factors, $validatedData) {
            foreach ($validatedData as $factorData) {
                $factor = $factors->firstWhere('id', $factorData['id']);

                if ($factor) {
                    $factor->sort_order = $factorData['sort_order'];

                    $factor->save();

                    if ($factor->is_optional && isset($factorData['options'])) {
                        foreach ($factorData['options'] as $optionData) {
                            $option = $factor->options->firstWhere('id', $optionData['id']);

                            if ($option) {
                                $option->sort_order = $optionData['sort_order'];
                                $option->save();
                            }
                        }
                    }
                }
            }

            return true;
        });
    }
}
