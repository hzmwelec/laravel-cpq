<?php

namespace Hzmwelec\CPQ\Validators;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SaveLeadtimeValidator
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
        $validator = Validator::make($data, [
            'title' => 'required|string|max:255',
            'hours' => 'required|integer|min:0',
            'condition' => 'present|string',
            'description' => 'present|string',
            'sort_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
