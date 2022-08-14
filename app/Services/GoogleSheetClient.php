<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Exception;
use JetBrains\PhpStorm\ArrayShape;

class GoogleSheetClient
{

    /**
     * @throws Exception
     */
    public static function getGoogleCredentials(): Client
    {
        $client = new Client;
        $config = storage_path().'/credentials.json';
        $appName = config('gconfig.google_app_name');
        $client->setApplicationName($appName);
        $client->setScopes(\Google_Service_Sheets::SPREADSHEETS);
        $client->addScope(\Google\Service\Drive::DRIVE);
        $client->setAuthConfig($config);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        return $client;
    }


    /**
     * @throws Exception
     */
    #[ArrayShape(["success" => "string"])] public function postToGoogleSheet($xmlData)
    {
        $client = self::getGoogleCredentials();
        $client->useApplicationDefaultCredentials();
        $service = new \Google_Service_Sheets($client);
        $spreadSheetId = config('gconfig.google_sheet_id');
        $values = $xmlData;
        $body = new \Google_Service_Sheets_ValueRange([
            'values' => $values
        ]);
        $params = ['valueInputOption' => 'RAW'];
        $insert = ['insertDataOption' => 'INSERT_ROWS'];
        $range = 'XML-TAB!A1:Z1000';
        try{
            $spreadSheet = $service->spreadsheets_values->append($spreadSheetId, $range, $body, $params);

            return ["success" => "Data uploaded successfully"];
        }catch (\Exception $e){
            echo 'Message ' . $e->getMessage();
        }
    }


}
