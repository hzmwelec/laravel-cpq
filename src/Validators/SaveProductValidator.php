<?php

namespace Hzmwelec\CPQ\Validators;

use Hzmwelec\CPQ\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SaveProductValidator
{
    /**
     * @param int $versionId
     * @param array $data
     * @param int $excludeId
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public static function validate($versionId, $data, $excludeId = 0)
    {
        $productTableName = (new Product())->getTable();

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique($productTableName)->when($excludeId, function ($rule) use ($excludeId) {
                    return $rule->ignore($excludeId);
                })->where(function ($query) use ($versionId) {
                    return $query->where('version_id', $versionId);
                }),
            ],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
