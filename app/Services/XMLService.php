<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Google\Exception;
use Google\Service\Oauth2\Userinfo;
use Google_Client;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\GoogleSheetClient;
use JsonException;

class XMLService
{
    /**
     * @return Google_Client
     * @throws Exception
     */
    public static function googleClient(): Google_Client
    {
        $config = storage_path().'/gweb.json';
        $appName = config('gconfig.google_app_name');
        $client = new Google_Client();
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

    /**
     * @return string
     * @throws Exception
     */
    public function googleAuthURL(): string
    {
        return $this->googleClient()->createAuthUrl();
    }

    /**
     * @param $request
     * @return JsonResponse|string
     * @throws Exception
     */
    public function authentication($request): JsonResponse|string
    {
        $authCode = urldecode($request->input('code'));
        $client = $this->googleClient();
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
        if(isset($accessToken['error'])){
            return
                $this->googleAuthURL();
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
        }
        $this->updateUser($user, $client, $accessToken);

        $token = $user->createToken('Google')->accessToken;
        return response()->json(
            [
                'message' => 'Access token must be added to the cli command',
                'access_token' =>$accessToken['access_token'],
                'authentication_token' => $token
            ], 201
        );
    }

    /**
     * @param $user
     * @param $client
     * @param $accessToken
     * @return void
     */
    public function updateUser($user, $client, $accessToken): void
    {
        $time = strtotime(Carbon::now()->format('H:i:s')) + $accessToken['expires_in'];
        $this->setAccessToken($client, $accessToken['access_token']);
        $user->google_access_token = $accessToken['access_token'];
        $user->google_refresh_token = $accessToken['refresh_token'];
        $user->expires_in = strtotime(date(Carbon::now()->format('Y-m-d ')) . date('H:i:s', $time));
        $user->save();
    }

    /**
     * @param $client
     * @param $token
     * @return Userinfo
     */
    public function setAccessToken($client, $token): Userinfo
    {
        $client->setAccessToken($token);
        $service = new \Google\Service\Oauth2($client);
        return $service->userinfo->get();
    }

    /**
     * @param $data
     * @return string|string[]|void
     * @throws Exception
     * @throws JsonException
     */
    public function uploadXMLToGoogleSheet($data)
    {
        $modes = ['auth', 'cli'];
        if(!in_array($data['mode'], $modes, true)){
            return ['error' => 'Sorry invalid mode entered. use either auth or cli'];
        }
        if($data['mode'] == 'auth') {
            $user = Auth::user();
        }else{
            $user = User::where('google_access_token', $data['token'])->where('provider', 'google')->first();
        }

        if($user === null){
            return  $this->googleAuthURL();
        }
        if(!$user->google_refresh_token){
            return  $this->googleAuthURL();
        }

        if(((strtotime(Carbon::now()) - $user->expires_in)) > 0){
            return $this->googleAuthURL();
        }

        $client = $this->googleClient();
        $accessToken = $client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);

        if(isset($accessToken['error'])){
            return $this->googleAuthURL();
        }

        $this->setAccessToken($client, $accessToken['access_token']);
        $this->updateUser($user, $client, $accessToken);
        if($data['type'] == 'remote'){
           // return remote
            //return (new GoogleSheetClient)->postToGoogleSheet($this->processRemoteXMLFile($data['file']));
        }

        if($this->processLocalXMLFile($data['file']) !== "Sorry could not process your file"){
            return (new GoogleSheetClient)->postToGoogleSheet($this->processLocalXMLFile($data['file']));
        }
        else{
            return  $this->processLocalXMLFile($data['file']);
        }

    }

    /**
     * @param $file
     * @return array
     * @throws JsonException
     */
    public function processLocalXMLFile($file): array
    {
        $result = $header = [];
        if(file_get_contents($file)) {
            $xmlObject = simplexml_load_string(file_get_contents($file), 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_PARSEHUGE | LIBXML_NOCDATA);
            if (!$xmlObject) {
                return ['Sorry'];
            }
            $xmlJson = json_decode(json_encode($xmlObject, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
            if (empty($xmlJson)) {
                return ['sorry'];
            }
            foreach ($xmlJson as $key => $itemArray) {
                foreach ($itemArray as $newKey => $newValue) {
                    foreach ($newValue as $lastKey => $value) {
                        $header [] = $lastKey;
                        if (is_array($value)) {
                            $value = implode(',', $value);
                        }
                        $result[$newKey][] = $value;
                    }
                }
            }

            array_unshift($result, array_unique($header));
            return $result;
        }else{
            return "Sorry could not process your file";
        }
    }



    public function sendSMS($url, $phoneNumber, $type = null)
    {
        $client = new GuzzleClient(["base_uri" => $url]);
        $payload = $this->payload();
        $response = $client->get("/", $payload);
        return $response->getBody();
    }

    /**
     * payload
     *
     * @return array
     */
    protected function payload(): array
    {
        return [
            "headers" => $this->httpHeader()
        ];
    }


    /**
     * @return string[]
     */
    protected function httpHeader(): array
    {
        return [
            "Authorization" => '',
            "Content-Type" => "application/json",
            "Accept: application/json"
        ];
    }
}
