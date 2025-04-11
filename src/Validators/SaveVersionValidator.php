<?php

namespace Hzmwelec\CPQ\Validators;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SaveVersionValidator
{
    /**
     * @param array $data
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public static function validate($data)
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
