<?php

namespace App\Validators;
use Illuminate\Http\Exceptions\HttpResponseException;

class ValidatorResponse
{
    /**
     * @param $error
     * @return mixed
     */
    public static function validationErrors($error): mixed
    {
        $errorResponse = response()->json([
            'error' => 'The given data was invalid',
            'message' => $error,
        ], 422);

        throw new HttpResponseException($errorResponse);
    }


    /**
     * @param $values
     * @return bool|mixed
     */
    public static function isArray($values): mixed
    {
        if (is_array($values)) {
            return true;
        }
        return self::validationErrors([$values => [str_replace("_", " ", $values)." must be an array"]]);
    }


    /**
     * @param $attributes
     * @param $values
     * @return bool|mixed
     */
    public static function arrayChecker($attributes, $values): mixed
    {

        if (is_array($values)) {
            foreach ($values as $key => $value) {
                if (is_numeric($value)) {
                    continue;
                }
                    $num = (int)$key+1;
                   return self::validationErrors([$attributes => ["Contains invalid characters, check element $num" ]]);
            }
        }
        return self::isArray($values);
    }


    /**
     * @param $attributes
     * @param $values
     * @return bool|mixed
     */
    public static function arrayCounter($attributes, $values): mixed
    {
        if (is_array($values)) {
            if(count($values) === count(array_unique($values))){
                return true;
            }else{
                return self::validationErrors([$attributes => ["keyword ids cannot contain same value"]]);
            }
        }
        return self::isArray($values);
    }
}
