<?php

namespace Hzmwelec\CPQ\Validators;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SortProductValidator
{
    /**
     * @param array $data
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public static function validate($data)
    {
        $validator = Validator::make($data, [
            '*.id' => 'required|integer|min:0',
            '*.sort_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
