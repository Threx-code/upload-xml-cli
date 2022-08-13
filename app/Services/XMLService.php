<?php

namespace App\Services;

class XMLService
{
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
