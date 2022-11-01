<?php

namespace App\Http\Controllers;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Client\AmoCRMApiRequest;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Models\LeadModel;
use Illuminate\Http\Request;
use League\OAuth2\Client\Token\AccessTokenInterface;
use ReflectionClass;
use App\Models\Lead;

class amocrm_test_request extends Controller
{

    public function test()
    {
        return view();
    }


    private function getToken($json_str)
    {
        $accessToken = json_decode($json_str, true);

        if (
            isset($accessToken)
            && isset($accessToken['accessToken'])
            && isset($accessToken['refreshToken'])
            && isset($accessToken['expires'])
            && isset($accessToken['baseDomain'])
        ) {
            return new \League\OAuth2\Client\Token\AccessToken([
                'access_token' => $accessToken['accessToken'],
                'refresh_token' => $accessToken['refreshToken'],
                'expires' => $accessToken['expires'],
                'baseDomain' => $accessToken['baseDomain'],
            ]);
        } else {
            exit('Invalid access token ' . var_export($accessToken, true));
        }
    }

    private function saveToken($accessToken)
    {
        if (
            isset($accessToken)
            && isset($accessToken['accessToken'])
            && isset($accessToken['refreshToken'])
            && isset($accessToken['expires'])
            && isset($accessToken['baseDomain'])
        ) {
            $data = [
                'accessToken' => $accessToken['accessToken'],
                'expires' => $accessToken['expires'],
                'refreshToken' => $accessToken['refreshToken'],
                'baseDomain' => $accessToken['baseDomain'],
            ];
            define('TOKEN_FILE', DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'token_info.json');

            file_put_contents(TOKEN_FILE, json_encode($data));
        } else {
            exit('Invalid access token ' . var_export($accessToken, true));
        }
    }

    private function json_encode_private($object) {

         $extract_props = function($object) use (&$extract_props){
            $public = [];

            $reflection = new ReflectionClass(get_class($object));

            foreach ($reflection->getProperties() as $property) {
                $property->setAccessible(true);

                $value = $property->getValue($object);
                $name = $property->getName();

                if(is_array($value)) {
                    $public[$name] = [];

                    foreach ($value as $item) {
                        if (is_object($item)) {
                            $itemArray = $extract_props($item);
                            $public[$name][] = $itemArray;
                        } else {
                            $public[$name][] = $item;
                        }
                    }
                } else if(is_object($value)) {
                    $public[$name] = $extract_props($value);
                } else $public[$name] = $value;
            }

            return $public;
        };

        return json_encode($extract_props($object));
    }
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    public function __invoke(Request $request): string
    {

        $raw_json = '{"accessToken":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImM3YmZlNzc3N2RkNGU5MjYxMzQxNDVhMWYyMTk0MmU0ZjAxMzI2ZjQ2ZTViZGNiMjgwM2QxM2YwZWNmNDIxY2E2NWUyNTNhY2FjOWVlNDQyIn0.eyJhdWQiOiI2ZTg0ZThlOS04Y2JmLTQzNmItOTQwMS1jNmExZWM5MDNhNTYiLCJqdGkiOiJjN2JmZTc3NzdkZDRlOTI2MTM0MTQ1YTFmMjE5NDJlNGYwMTMyNmY0NmU1YmRjYjI4MDNkMTNmMGVjZjQyMWNhNjVlMjUzYWNhYzllZTQ0MiIsImlhdCI6MTY2NzMyNDcwMSwibmJmIjoxNjY3MzI0NzAxLCJleHAiOjE2Njc0MTExMDEsInN1YiI6Ijg4MDI5MTYiLCJhY2NvdW50X2lkIjozMDU5MTM3MCwiYmFzZV9kb21haW4iOiJhbW9jcm0ucnUiLCJzY29wZXMiOlsicHVzaF9ub3RpZmljYXRpb25zIiwiZmlsZXMiLCJjcm0iLCJmaWxlc19kZWxldGUiLCJub3RpZmljYXRpb25zIl19.Eyfx1rkV8zXvik3FBNLQiZThmUAHi25BC4s-5iNPCPlL8bR9s3J0CIl_n9pcOJuGq5TvRiI7a0oJ7MasvBYPSodhRrqogJwveJmcJPDtsW8hGqieKPi12dy_09yskYBLnc_nHQSfGPKDssg9aUA9UGLYQ5iQxaMWGt1sVmVhX2pL4dT6fcR7HFL60JCbGc5ERngPTOygJqpMtJtZbYa-o-cAXySC0wm6SmYjH2gkYoJkSwYDeY99NB7Y5ihCmiCmpm1Fs6JqA_R_dEOksN4iAnwqi38fUVcHHGKVDzNS91fnK4xNvtEiC_OH-Ob03GqPsCtUmR_Vj1Xzr5mpIGpnwQ","refreshToken":"def5020023e969b255d5452619ec7afff19a91389276c53c3e3a9499f4d55ce1d96782bb91d373d17b446839ff36e15e955a098668a1f31a994a8dc91e251800460792ba7d3dcd6b09f8dce0d1719d4df4385b94b0a4193b2bdc753d096a58be5861498ca4ef4c1dbfc83261cd789104b01f6d4ebe6d67efef350516c8ff0bb6d500837f6fd48bf8c411a5f1f9c24ad076f7868efa98f56168fc5422b2a845facbbb5c8b5028e1ba495b7719cb52f1b8260859f0caa48ddd4c3dc446f98baec12a71ecd004c0950c3c6b507d58c5cd050ae331db07331b26d7241cd33e9941d915c3f58bccb0826c3b657e07abc50d941e9d4447934c301e6968b98636bdacef5b9d1032fa059c9e0c8534c57d5f5cce2efc2c0d9cf6036493900af9d233528bba24fbb278d911f11f78104bc826f74d967d2358f75adbfb234c928e03d3c6b8bab46193b5f4119bbb335eb090041690a57674d8fd15d039176b1abdc46af7dd637654483b0eab4f30d288bc05bd83745acea3c3e1f507b047ab65c4abaee3d99e0b8b2e07756ae16082da8e47f7d30e62a46ff45fba58594ada4c7aa95e9eacfb3efb6810fd5b63614bca9f270118fe844e9156eefd7e6766540051d62b3ce0f4f602d2173382ebb760db08a1272ccefab3f9d4fc60a1605a34d47098e43145dc","expires":1667411102,"baseDomain":"andrushachel.amocrm.ru"}';
        $test_json = json_decode($raw_json);
        $apiClient = new \AmoCRM\Client\AmoCRMApiClient('6e84e8e9-8cbf-436b-9401-c6a1ec903a56', 'OSBJFYKyxhruHucuz62Op3ZA5XYeFKLSqAZdXuDQXw9JDGKO7hk22IYZKDrQ2tRt', "https://sky-e.ru/secret");
        $apiClient->setAccountBaseDomain('andrushachel.amocrm.ru')->setAccessToken($this->getToken($raw_json))->onAccessTokenRefresh(
            function (AccessTokenInterface $accessToken, string $baseDomain) {
                $this->saveToken(
                    [
                        'accessToken' => $accessToken->getToken(),
                        'refreshToken' => $accessToken->getRefreshToken(),
                        'expires' => $accessToken->getExpires(),
                        'baseDomain' => $baseDomain,
                    ]
                );
            }
        );

        try {
            $leadsService = $apiClient->leads();
            $leadsCollection = $leadsService->get(null, ['contacts', 'source_id', LeadModel::CATALOG_ELEMENTS]);

            foreach ($leadsCollection as $item) {
                if ($item !== null){
                    $l = LeadModel::fromArray($item->toArray());
                    $lead = new Lead();
//                    if (Lead::find($l->getId()))
//                        continue;
                    $lead->id = $l->getId();
                    $lead->name = $l->getName();
                    $lead->price = $l->getPrice();
                    $lead->responsible_user_id = $l->getResponsibleUserId();
                    $lead->group_id = $l->getGroupId();
                    $lead->status_id = $l->getStatusId();
                    $lead->pipeline_id = $l->getPipelineId();
                    $lead->source_id = (int) $l->getSourceExternalId();
                    $lead->tags = json_encode($l->getTags()?->jsonSerialize() ?? null);
                    $lead->loss_reason_id = $l->getLossReason()?->getId() ?? null;
                    $lead->company_id = $l->getCompany()?->getId() ?? null;
                    $lead->catalog_elements = $l->getCatalogElementsLinks()?->jsonSerialize() ?? null;
                    $lead->refresh();
                }
            }

            return Lead::All();

        } catch (AmoCRMApiException $e) {
            return (string) $e;
        }
    }


}
