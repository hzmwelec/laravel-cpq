<?php

namespace Hzmwelec\CPQ\Services;

use Hzmwelec\CPQ\Exceptions\RuntimeException;
use Hzmwelec\CPQ\Models\Version;
use Hzmwelec\CPQ\Validators\SaveVersionValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VersionService
{
    /**
     * @var int $page
     * @var int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginateVersions($page, $perPage = 20)
    {
        return Version::orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @param int $versionId
     * @param array $relations
     * @return \Hzmwelec\CPQ\Models\Version
     * @throws \Hzmwelec\CPQ\Exceptions\RuntimeException
     */
    public function getVersion($versionId, $relations = [])
    {
        $version = Version::find($versionId);

        if (!$version) {
            throw new RuntimeException('Version not found');
        }

        $version->load($relations);

        return $version;
    }

    /**
     * @param array $data
     * @return \Hzmwelec\CPQ\Models\Version
     */
    public function createVersion($data)
    {
        $validatedData = SaveVersionValidator::validate($data);

        return Version::create(array_merge($validatedData, [
            'uuid' => Str::uuid()->toString(),
            'is_locked' => false,
            'is_active' => false,
        ]));
    }

    /**
     * @param int $versionId
     * @param array $data
     * @return bool
     * @throws \Hzmwelec\CPQ\Exceptions\RuntimeException
     */
    public function updateVersion($versionId, $data)
    {
        $version = Version::find($versionId);

        if (!$version) {
            throw new RuntimeException('Version not found');
        }

        if (!$version->is_editable) {
            throw new RuntimeException('Version is not editable');
        }

        $validatedData = SaveVersionValidator::validate($data);

        return $version->update($validatedData);
    }

    /**
     * @param int $versionId
     * @return bool
     * @throws \Hzmwelec\CPQ\Exceptions\RuntimeException
     */
    public function deleteVersion($versionId)
    {
        $version = Version::find($versionId);

        if (!$version) {
            throw new RuntimeException('Version not found');
        }

        if (!$version->is_deletable) {
            throw new RuntimeException('Version is not deletable');
        }

        return DB::transaction(function () use ($version) {
            foreach ($version->products as $product) {
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

                $product->delete();
            }

            return $version->delete();
        });
    }

    /**
     * @param int $versionId
     * @return bool
     * @throws \Hzmwelec\CPQ\Exceptions\RuntimeException
     */
    public function lockVersion($versionId)
    {
        $version = Version::find($versionId);

        if (!$version) {
            throw new RuntimeException('Version not found');
        }

        if (!$version->is_lockable) {
            throw new RuntimeException('Version is not lockable');
        }

        $version->is_locked = true;

        return $version->save();
    }

    /**
     * @param int $versionId
     * @return bool
     * @throws \Hzmwelec\CPQ\Exceptions\RuntimeException
     */
    public function unlockVersion($versionId)
    {
        $version = Version::find($versionId);

        if (!$version) {
            throw new RuntimeException('Version not found');
        }

        if (!$version->is_unlockable) {
            throw new RuntimeException('Version is not unlockable');
        }

        $version->is_locked = false;

        return $version->save();
    }

    /**
     * @param int $versionId
     * @return bool
     * @throws \Hzmwelec\CPQ\Exceptions\RuntimeException
     */
    public function activateVersion($versionId)
    {
        $version = Version::find($versionId);

        if (!$version) {
            throw new RuntimeException('Version not found');
        }

        if (!$version->is_activable) {
            throw new RuntimeException('Version is not activable');
        }

        $version->is_active = true;

        return $version->save();
    }

    /**
     * @param int $versionId
     * @return \Hzmwelec\CPQ\Models\Version
     * @throws \Hzmwelec\CPQ\Exceptions\RuntimeException
     */
    public function replicateVersion($versionId)
    {
        $version = Version::find($versionId);

        if (!$version) {
            throw new RuntimeException('Version not found');
        }

        return DB::transaction(function () use ($version) {
            $newVersion = $version->replicate();

            $newVersion->save();

            foreach ($version->products as $product) {
                $newProduct = $this->replicateProduct($product, $newVersion->id);

                foreach ($product->factors as $factor) {
                    $this->replicateFactor($factor, $newProduct->id);
                }

                foreach ($product->costs as $cost) {
                    $this->replicateCost($cost, $newProduct->id);
                }

                foreach ($product->leadtimes as $leadtime) {
                    $this->replicateLeadtime($leadtime, $newProduct->id);
                }
            }

            return $newVersion;
        });
    }

    /**
     * @param \Hzmwelec\CPQ\Models\Product $product
     * @param int $versionId
     * @return \Hzmwelec\CPQ\Models\Product
     */
    protected function replicateProduct($product, $versionId)
    {
        $newProduct = $product->replicate();

        $newProduct->version_id = $versionId;

        $newProduct->save();

        return $newProduct;
    }

    /**
     * @param \Hzmwelec\CPQ\Models\Factor $factor
     * @param int $productId
     * @return \Hzmwelec\CPQ\Models\Factor
     */
    protected function replicateFactor($factor, $productId)
    {
        $newFactor = $factor->replicate();

        $newFactor->product_id = $productId;

        $newFactor->save();

        foreach ($factor->options as $option) {
            $newOption = $option->replicate();

            $newOption->factor_id = $newFactor->id;

            $newOption->save();
        }

        return $newFactor;
    }

    /**
     * @param \Hzmwelec\CPQ\Models\Cost $cost
     * @param int $productId
     * @return \Hzmwelec\CPQ\Models\Cost
     */
    protected function replicateCost($cost, $productId)
    {
        $newCost = $cost->replicate();

        $newCost->product_id = $productId;

        $newCost->save();

        foreach ($cost->rules as $rule) {
            $newRule = $rule->replicate();

            $newRule->cost_id = $newCost->id;

            $newRule->save();
        }

        return $newCost;
    }

    /**
     * @param \Hzmwelec\CPQ\Models\Leadtime $leadtime
     * @param int $productId
     * @return \Hzmwelec\CPQ\Models\Leadtime
     */
    protected function replicateLeadtime($leadtime, $productId)
    {
        $newLeadtime = $leadtime->replicate();

        $newLeadtime->product_id = $productId;

        $newLeadtime->save();

        return $newLeadtime;
    }
}
