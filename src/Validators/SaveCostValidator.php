<?php

namespace Hzmwelec\CPQ\Validators;

use Hzmwelec\CPQ\Models\Cost;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SaveCostValidator
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
        $costTableName = (new Cost())->getTable();

        $validator = Validator::make($data, [
            'title' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique($costTableName)->when($excludeId, function ($rule) use ($excludeId) {
                    return $rule->ignore($excludeId);
                })->where(function ($query) use ($productId) {
                    return $query->where('product_id', $productId);
                }),
            ],
            'sort_order' => 'required|integer|min:0',
            'rules' => 'required|array',
            'rules.*.action' => 'required|string',
            'rules.*.condition' => 'present|string',
            'rules.*.description' => 'present|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
