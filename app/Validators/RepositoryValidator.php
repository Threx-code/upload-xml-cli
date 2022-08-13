<?php

namespace App\Validators;

use Illuminate\Http\Exceptions\HttpResponseException;

class RepositoryValidator
{

    /**
     * @return mixed
     */
    public static function dataAlreadyExist(): mixed
    {
        $errorResponse = response()->json([
            'error' => 'insertion error',
            'message' => 'The given data already exist',
        ], 409);

        throw new HttpResponseException($errorResponse);
    }

    /**
     * @param $ids
     * @return mixed
     */
    public static function couldNotDelete($ids = null): mixed
    {
            $errorResponse = response()->json([
                'error' => 'deletion error',
                'message' => 'The given data could not be deleted',
                'undeleted_ids' => $ids

            ], 409);

        throw new HttpResponseException($errorResponse);
    }

    /**
     * @return mixed
     */
    public static function dataDoesNotExist(): mixed
    {
        $errorResponse = response()->json([
            'error' => 'Data Not Found',
            'message' => 'The given data does not exist',
        ], 404);

        throw new HttpResponseException($errorResponse);
    }


}
