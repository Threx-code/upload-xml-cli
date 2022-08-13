<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Google\Exception;
use Google\Service\Oauth2\Userinfo;
use Google_Client;
use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\GoogleSheetClient;
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
            ], 201);
    }


    /**
     * @throws Exception
     * @throws \JsonException
     */
    public function uploadXMLToGoogleSheet($data)
    {
        $user = Auth::user();
        if(!$user){
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

        $googleUser = $this->setAccessToken($client, $accessToken['access_token']);
        $this->updateUser($user, $client, $accessToken);
        return (new GoogleSheetClient)->postToGoogleSheet($this->processXMLFile($data));

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
        $googleUser = $this->setAccessToken($client, $accessToken['access_token']);
        $user->google_access_token = $googleUser['access_token'];
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

    public function processXMLFile($file)
    {
        $result =[];
        $xmlObject = simplexml_load_string(file_get_contents($file), 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_PARSEHUGE|LIBXML_NOCDATA);
        $json = json_decode(json_encode($xmlObject, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        foreach($json as $key => $value){
            foreach($value as $newKey => $newValue){
                //$result [] = $newValue;
                foreach($newValue  as $hhh){
                    if(is_array($hhh)){
                        $hhh = implode(',', $hhh);
                    }
                    $result[$newKey][] = $hhh;
                }
            }
        }

        return $result;
    }



    public function sendSMS($otp, $phoneNumber, $type = null)
    {
        $client = new Client(["base_uri" => getenv('INFOBIP_URL_BASE_PATH')]);
        $payload = $this->payload($otp, $phoneNumber, $type);
        $response = $client->post("/sms/2/text/advanced", $payload);
        return $response->getBody();
    }

    /**
     * payload
     *
     * @return array
     */
    protected function payload($otp, $phoneNumber, $type = null): array
    {
        return [
            "json" => $this->smsBody($otp, $phoneNumber, $type),
            "headers" => $this->httpHeader()
        ];
    }


    /**
     * httpHeader
     *
     * @return array
     */
    protected function httpHeader(): array
    {
        return [
            "Authorization" => getenv('INFOBIP_API_KEY_PREFIX') . " " . getenv('INFOBIP_API_KEY'),
            "Content-Type" => "application/json",
            "Accept: application/json"
        ];
    }

    /**
     * smsBody
     *
     * @return array
     */
    protected function smsBody($otp, $phoneNumber, $type = null): array
    {
        if($type === "signup"){
            $message = "Your Gokada OTP is $otp. Please keep it safe and do not share it with anyone. Expires in 60 minutes";
        }else{
            $message = "Your Gokada order dropoff OTP is $otp. Please keep it safe and provide it to the driver when collecting your package.";
        }

        return [
            'messages' => [
                [
                    'from' => "GokadaNG",
                    'text' => $message,
                    'destinations' => [
                        [
                            'to' => $phoneNumber,
                        ],
                    ],
                ],
            ],
        ];
    }

}
