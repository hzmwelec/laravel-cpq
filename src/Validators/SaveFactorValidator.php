<?php

namespace Hzmwelec\CPQ\Validators;

use Hzmwelec\CPQ\Models\Factor;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SaveFactorValidator
{
    /**
     * @param int $productId
     * @param array $data
     * @param int $excludeId
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public static function validate($productId, $data, $excludeId = 0)
    {
        $factorTableName = (new Factor())->getTable();

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique($factorTableName)->when($excludeId, function ($rule) use ($excludeId) {
                    return $rule->ignore($excludeId);
                })->where(function ($query) use ($productId) {
                    return $query->where('product_id', $productId);
                }),
            ],
            'description' => 'present|string|max:255',
            'is_optional' => 'required|bool',
            'sort_order' => 'required|integer|min:0',
            'options' => 'nullable|array|required_if:is_optional,false',
            'options.*.name' => 'required|string|max:255',
            'options.*.code' => 'required|string|max:255',
            'options.*.description' => 'present|string|max:255',
            'options.*.sort_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
