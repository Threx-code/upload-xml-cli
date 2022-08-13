<?php

namespace App\Contracts;

interface XMLInterface
{
    public static function callGoogleAPI($request);
    public static function localXML($request);
    public static function remoteXML($request);
    public static function authenticateUser($request);
}
