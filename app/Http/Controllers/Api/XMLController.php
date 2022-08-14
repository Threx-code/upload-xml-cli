<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserAuthentication;
use App\Http\Requests\XMLUploadRequest;
use Illuminate\Http\Request;
use App\Contracts\XMLInterface;


class XMLController extends Controller
{
    private static XMLInterface $repository;

    public function __construct(XMLInterface $repository)
    {
        self::$repository = $repository;
    }

    /**
     * @return mixed
     */
    public function getAuthUrl(): mixed
    {
        return self::$repository::getGoogleURL();
    }

    /**
     * @param UserAuthentication $request
     * @return mixed
     */
    public function authenticateUser(UserAuthentication $request): mixed
    {
        return self::$repository::authenticateUser($request);
    }

    /**
     * @param XMLUploadRequest $request
     * @return mixed
     */
    public function uploadXMLFile(XMLUploadRequest $request): mixed
    {
        $file = $request->file('file');
        $type = $request->type;
        $url = $request->url;
        $data = [
            'mode' => 'auth',
            'type' =>  $type,
            'url' => $url,
            'file' => $file,
        ];

        return self::$repository::uploadXMLFileToGoogleSheet ($data);
    }















}
