<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Http\Request;
use App\Contracts\XMLInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class XMLController extends Controller
{
    private static XMLInterface $repository;
    private static Client $client;


    public function __construct(XMLInterface $repository, Client $client)
    {
        self::$repository = $repository;
        self::$client = $client;
    }

    public function googleClient(): \Google_Client
    {
        $config = base_path().'/gweb.json';
        $appName = 'XML-Test';
        $client = new \Google_Client();
        $client->setApplicationName($appName);
        $client->setAuthConfig($config);
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        $client->setScopes([
            \Google\Service\Oauth2::USERINFO_PROFILE,
            \Google\Service\Oauth2::USERINFO_EMAIL,
            \Google\Service\Oauth2::OPENID,
            \Google\Service\Drive::DRIVE_METADATA_READONLY
        ]);
        $client->setIncludeGrantedScopes(true);
        return $client;
    }

    public function authURL(Request $request)
    {
        $client = $this->googleClient();
        $authUrl = $client->createAuthUrl();
        return $authUrl;
    }


    public function auththentication(Request $request)
    {
        $authCode = urldecode($request->input('code'));
        $client = $this->googleClient();
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
        if(isset($accessToken['error'])){
            return
                $this->authURL($request);
        }

        // set the access token
        $googleUser = $this->setAccessToken($client, $accessToken['access_token']);

        // check if user exists
        $user = User::where('provider', 'google')
            ->where('provider_id', $googleUser->id)
            ->first();

        if(!$user){
            $time = strtotime(Carbon::now()->format('H:i:s')) + $accessToken['expires_in'];
            $user = User::create([
                'provider_id' => $googleUser->id,
                'provider' => 'google',
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'password' => Hash::make('password'),
                'google_access_token' => $accessToken['access_token'],
                'google_refresh_token' => $accessToken['refresh_token'],
                'expires_in' => strtotime(date(Carbon::now()->format('Y-m-d ')) . date('H:i:s', $time))
            ]);
            $token = $user->createToken('Google')->accessToken;
            return response()->json($token, 201);
        }
        return $this->updateUser($user, $client, $accessToken);
    }


    /**
     * @throws \Google\Exception
     */
    public function getGoogleCredentials()
    {
        $config = base_path().'/credentials.json';
        self::$client->setApplicationName('XML Google Sheet');
        self::$client->setScopes(\Google_Service_Sheets::SPREADSHEETS);
        self::$client->addScope(\Google\Service\Drive::DRIVE);
        self::$client->setAuthConfig($config);
        self::$client->setAccessType('offline');
        self::$client->setPrompt('select_account consent');
        return self::$client;
    }

    public function postToGoogleSheet()
    {
        $this->getGoogleCredentials()->useApplicationDefaultCredentials();
        $service = new \Google_Service_Sheets(self::$client);
        $spreadSheetId = '18l6EDNjRCW50ZIu88km24TP-G5NGY-OZPSZ1lJR9sYI';
        $values = [["first", "second", "third"]];
        $body = new \Google_Service_Sheets_ValueRange([
            'values' => $values
        ]);
        $params = [
            'valueInputOption' => 'RAW'
        ];
        $insert = [
            'insertDataOption' => 'INSERT_ROWS'
        ];
        $updateRange = 'XML-TAB!A1:Z1000';
        try{
            $spreadSheet = $service->spreadsheets_values->append($spreadSheetId, $updateRange, $body, $params);

            print_r($spreadSheet);
            return $spreadSheet->spreadsheetId;
        }catch (\Exception $e){
            echo 'Message ' . $e->getMessage();
        }
    }

    public function revalidateAccessToken(Request $request)
    {
        $user = Auth::user();
        if(!$user){
            return  $this->authURL($request);
        }

        if(((strtotime(Carbon::now()) - $user->expires_in)) > 0){
           return $this->authURL($request);
        }

        $client = $this->googleClient();
        $accessToken = $client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);

        if(isset($accessToken['error'])){
            return
                $this->authURL($request);
        }

        $googleUser = $this->setAccessToken($client, $accessToken['access_token']);
        $this->postToGoogleSheet();
       return $this->updateUser($user, $client, $accessToken);


    }

    /**
     * @param $client
     * @param $token
     * @return \Google\Service\Oauth2\Userinfo
     */
    public function setAccessToken($client, $token)
    {
        $client->setAccessToken($token);
        $service = new \Google\Service\Oauth2($client);
        return $service->userinfo->get();
    }

    public function updateUser($user, $client, $accessToken)
    {
        $time = strtotime(Carbon::now()->format('H:i:s')) + $accessToken['expires_in'];
        $googleUser = $this->setAccessToken($client, $accessToken['access_token']);
        $user->google_access_token = $googleUser['access_token'];
        $user->expires_in = strtotime(date(Carbon::now()->format('Y-m-d ')) . date('H:i:s', $time));
        $user->save();
        return response()->json(["success" =>"user data updated"], 200);
    }

}
