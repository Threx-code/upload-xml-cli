<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Exception;

class GoogleSheetClient
{
    private static Client $client;

    public function __construct(Client $client)
    {
        self::$client = $client;
    }

    /**
     * @throws Exception
     * @throws \JsonException
     */
    public function getGoogleCredentials()
    {
        self::$client->setApplicationName('XML Google Sheet');
        self::$client->setScopes(Drive::DRIVE);
        self::$client->setAuthConfig(__dir__.'/credentials.json');
        self::$client->setAccessType('offline');
        self::$client->setPrompt('select_account consent');
        return self::$client;
    }

    public function createTitle()
    {
        $code = '908145d6944ed38fe3fef8daa7d1317c788ef842';
        if(isset($code)){
            $token = self::$client->fetchAccessTokenWithAuthCode($code);
        }
        $this->getGoogleCredentials()->useApplicationDefaultCredentials();
        $service = new \Google_Service_Sheets(self::$client);
        try{
            $spreadSheet = new \Google_Service_Sheets_Spreadsheet([
                'properties' => [
                    'title' => 'first title2'
                ]
            ]);
            $spreadSheet = $service->spreadsheets->create($spreadSheet, [
                'fields' => 'spreadsheetId'
            ]);
            printf('Spreadsheet Id: %s\n', $spreadSheet->spreadsheetId);
            return $spreadSheet;
        }catch (\Exception $e){
            echo 'Message ' . $e->getMessage();
        }
    }


}
