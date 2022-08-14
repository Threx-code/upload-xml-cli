<?php

namespace App\Repositories;
use App\Services\XMLService;
use Google\Exception;
use \Illuminate\Http\JsonResponse;
use App\Contracts\XMLInterface;
use JsonException;

class XMLRepository implements XMLInterface
{
    /**
     * @param $request
     * @return JsonResponse|string
     * @throws Exception
     */
    public static function authenticateUser($request): JsonResponse|string
    {
        return (new XMLService)->authentication($request);
    }

    /**
     * @throws Exception
     */
    public static function getGoogleURL(): string
    {
        return (new XMLService)->googleAuthURL();
    }

    /**
     * @param $data
     * @return string|void
     * @throws Exception
     * @throws JsonException
     */
    public static function uploadXMLFileToGoogleSheet($data)
    {
        return (new XMLService)->uploadXMLToGoogleSheet($data);
    }
}
