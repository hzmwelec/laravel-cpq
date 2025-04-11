<?php

namespace Hzmwelec\CPQ\Services;

use Hzmwelec\CPQ\Exceptions\RuntimeException;
use Hzmwelec\CPQ\Models\Product;
use Hzmwelec\CPQ\Models\Version;
use Hzmwelec\CPQ\Validators\SaveProductValidator;
use Hzmwelec\CPQ\Validators\SortProductValidator;
use Illuminate\Support\Facades\DB;

class ProductService
{
    /**
     * @param int $versionId
     * @param array $data
     * @return \Hzmwelec\CPQ\Models\Product
     * @throws \Hzmwelec\CPQ\Exceptions\RuntimeException
     */
    public function createProduct($versionId, $data)
    {
        $version = Version::find($versionId);

        if (!$version) {
            throw new RuntimeException('Version not found');
        }

        if (!$version->is_editable) {
            throw new RuntimeException('Version is not editable');
        }

        $validatedData = SaveProductValidator::validate($version->id, $data);

        return Product::create(array_merge($validatedData, [
            'version_id' => $version->id,
        ]));
    }

    /**
     * @param int $productId
     * @param array $data
     * @return bool
     * @throws \Hzmwelec\CPQ\Exceptions\RuntimeException
     */
    public function updateProduct($productId, $data)
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

        $validatedData = SaveProductValidator::validate($product->version_id, $data, $product->id);

        return $product->update($validatedData);
    }

    /**
     * @param int $productId
     * @return bool
     * @throws \Hzmwelec\CPQ\Exceptions\RuntimeException
     */
    public function deleteProduct($productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            throw new RuntimeException('Product not found');
        }

        $version = Version::find($product->version_id);

        if (!$version) {
            throw new RuntimeException("Product's version not found");
        }

        if (!$version->is_deletable) {
            throw new RuntimeException('Product is not deletable');
        }

        return DB::transaction(function () use ($product) {
            $product->leadtimes->each(function ($leadtime) {
                $leadtime->delete();
            });

            $product->costs->each(function ($cost) {
                $cost->rules()->delete();

                $cost->delete();
            });

            $product->factors->each(function ($factor) {
                $factor->options()->delete();

                $factor->delete();
            });

            return $product->delete();
        });
    }

    /**
     * @param int $versionId
     * @param array $data
     * @return bool
     * @throws \Hzmwelec\CPQ\Exceptions\RuntimeException
     */
    public function sortProducts($versionId, $data)
    {
        $version = Version::find($versionId);

        if (!$version) {
            throw new RuntimeException('Version not found');
        }

        if (!$version->is_editable) {
            throw new RuntimeException('Version is not editable');
        }

        $validatedData = SortProductValidator::validate($data);

        $products = Product::where('version_id', $version->id)->get();

        return DB::transaction(function () use ($products, $validatedData) {
            foreach ($validatedData as $productData) {
                $product = $products->firstWhere('id', $productData['id']);

                if ($product) {
                    $product->sort_order = $productData['sort_order'];

                    $product->save();
                }
            }

            return true;
        });
    }
}
