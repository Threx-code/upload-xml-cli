<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserAuthentication;
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
     * @param Request $request
     * @return mixed
     */
    public function uploadXMLFile(Request $request): mixed
    {
        // collect file from endpoint
        // determine th type of upload local/remote
        // local should require an upload
        // remote should require a link
        return self::$repository::uploadXMLFileToGoogleSheet($request);
    }















}
