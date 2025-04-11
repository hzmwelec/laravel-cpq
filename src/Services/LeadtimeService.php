<?php

namespace Hzmwelec\CPQ\Services;

use Hzmwelec\CPQ\Exceptions\RuntimeException;
use Hzmwelec\CPQ\Models\Leadtime;
use Hzmwelec\CPQ\Models\Product;
use Hzmwelec\CPQ\Models\Version;
use Hzmwelec\CPQ\Validators\SaveLeadtimeValidator;
use Hzmwelec\CPQ\Validators\SortLeadtimeValidator;
use Illuminate\Support\Facades\DB;

class LeadtimeService
{
    /**
     * @param int $productId
     * @param array $data
     * @return \Hzmwelec\CPQ\Models\Leadtime
     * @throws \Hzmwelec\CPQ\Exceptions\RuntimeException
     */
    public function createLeadtime($productId, $data)
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

        $validatedData = SaveLeadtimeValidator::validate($product->id, $data);

        return Leadtime::create(array_merge($validatedData, [
            'product_id' => $product->id,
        ]));
    }

    /**
     * @param int $leadtimeId
     * @param array $data
     * @return bool
     * @throws \Hzmwelec\CPQ\Exceptions\RuntimeException
     */
    public function updateLeadtime($leadtimeId, $data)
    {
        $leadtime = Leadtime::find($leadtimeId);

        if (!$leadtime) {
            throw new RuntimeException('Leadtime not found');
        }

        $product = Product::find($leadtime->product_id);

        if (!$product) {
            throw new RuntimeException("Leadtime's product not found");
        }

        $version = Version::find($product->version_id);

        if (!$version) {
            throw new RuntimeException("Leadtime's version not found");
        }

        if (!$version->is_editable) {
            throw new RuntimeException('Leadtime is not editable');
        }

        $validatedData = SaveLeadtimeValidator::validate($leadtime->product_id, $data, $leadtime->id);

        return $leadtime->update($validatedData);
    }

    /**
     * @param int $leadtimeId
     * @return bool
     * @throws \Hzmwelec\CPQ\Exceptions\RuntimeException
     */
    public function deleteLeadtime($leadtimeId)
    {
        $leadtime = Leadtime::find($leadtimeId);

        if (!$leadtime) {
            throw new RuntimeException('Leadtime not found');
        }

        $product = Product::find($leadtime->product_id);

        if (!$product) {
            throw new RuntimeException("Leadtime's product not found");
        }

        $version = Version::find($product->version_id);

        if (!$version) {
            throw new RuntimeException("Leadtime's version not found");
        }

        if (!$version->is_deletable) {
            throw new RuntimeException('Leadtime is not deletable');
        }

        return $leadtime->delete();
    }

    /**
     * @param int $productId
     * @param array $data
     * @return bool
     * @throws \Hzmwelec\CPQ\Exceptions\RuntimeException
     */
    public function multisortLeadtimes($productId, $data)
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

        $validatedData = SortLeadtimeValidator::validate($data);

        $leadtimes = Leadtime::where('product_id', $product->id)->get();

        return DB::transaction(function () use ($leadtimes, $validatedData) {
            foreach ($validatedData as $leadtimeData) {
                $leadtime = $leadtimes->firstWhere('id', $leadtimeData['id']);

                if ($leadtime) {
                    $leadtime->sort_order = $leadtimeData['sort_order'];

                    $leadtime->save();
                }
            }

            return true;
        });
    }
}
