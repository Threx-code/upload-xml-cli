<?php

namespace App\Contracts;

interface XMLInterface
{
    public static function getGoogleURL();
    public static function authenticateUser($request);
    public static function uploadXMLFileToGoogleSheet($data);
}
