<?php

namespace App\Http\Controllers;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Client\AmoCRMApiRequest;
use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\Customers\CustomersCollection;
use AmoCRM\Collections\TagsCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Models\CompanyModel;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\Customers\CustomerModel;
use AmoCRM\Models\LeadModel;
use AmoCRM\Models\Leads\LossReasons\LossReasonModel;
use AmoCRM\Models\RoleModel;
use AmoCRM\Models\SourceModel;
use AmoCRM\Models\TagModel;
use AmoCRM\Models\UserModel;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\Group;
use App\Models\LossReason;
use App\Models\Pipeline;
use App\Models\Source;
use App\Models\Status;
use App\Models\Tag;
use App\Models\User;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Client\Token\AccessTokenInterface;
use ReflectionClass;
use App\Models\Lead;

class amocrm_test_request extends Controller
{

    private AmoCRMApiClient $apiClient;

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

    private function write_tags(TagsCollection|null $tags): ?array
    {
        if ($tags === null)
            return null;

        $tags_ids = [];
        for ($j = 0; $j < $tags->count(); $j++){

            if($tags[$j] == null)
                continue;

            $tags_ids[] = $tags[$j]->getId();

            if (Tag::find($tags[$j]->getId()))
                continue;

            $tag = new Tag();
            $tag->id = $tags[$j]->getId();
            $tag->color = $tags[$j]->getColor();
            $tag->name = $tags[$j]->getName();
            $tag->save();
        }
        return $tags_ids;
    }

    private function write_user(UserModel $userModel): ?int
    {
        $userId = $userModel->getId();
        if(User::find($userModel->getId()))
            return $userId;
        $user = new User();
        $user->id = $userModel->getId();
        $user->email = $userModel->getEmail();
        $user->name = $userModel->getName();
        $user->save();
        return $userId;
    }

    private function write_user_by_id( int $user_id): ?int
    {
        return $this->write_user($this->apiClient->users()->getOne($user_id));
    }

    private function write_group_by_id( int $id): ?int
    {
        if ($id === 0)
            return 0;

        $roleModel = $this->apiClient->roles()->getOne($id);
        $roleId = $roleModel->getId();
        if (Group::find($roleId))
            return $roleId;
        $role = new Group();
        $role->id = $roleModel->getId();
        $role->name = $roleModel->getName();
        $role->save();
        return $roleId;
    }

    private function write_status_by_id( int $id, int $pipelineId): ?int
    {
        if ($id === 0)
            return 0;
        $statusModel = $this->apiClient->statuses($pipelineId)->getOne($id);
        $statusId = $statusModel->getId();
        if (Status::find($statusId))
            return $statusId;
        $status = new Status();
        $status->id = $statusModel->getId();
        $status->name = $statusModel->getName();
        $status->color = $statusModel->getColor();
        $status->pipeline_id = $pipelineId;
        $status->type = $statusModel->getType();
        $status->save();
        return $statusId;
    }


    private function write_pipeline_by_id( int $id): ?int
    {
        $pipelineModel = $this->apiClient->pipelines()->getOne($id);

        $pipelineId = $pipelineModel->getId();

        if (Pipeline::find($pipelineId))
            return $pipelineId;
        $pipeline = new Pipeline;

        $pipeline->id = $pipelineModel->getId();
        $pipeline->name = $pipelineModel->getName();
        $pipeline->is_main = $pipelineModel->getIsMain();
        $pipeline->sort = $pipelineModel->getSort();
        $pipeline->account_id = $pipelineModel->getAccountId();
        $pipeline->save();
        return $pipelineId;
    }

    private function write_source_by_id( int|null $id)
    {
        if ($id == null)
            return null;
        $sourceModel = $this->apiClient->sources()->getOne($id);

        $sourceId = $sourceModel->getId();

        if (Source::find($sourceId))
            return $sourceId;
        $source = new Source();

        $source->id = $sourceModel->getId();
        $source->name = $sourceModel->getName();
        $source->pipeline_id = $sourceModel->getPipelineId();
        $source->external_id = $sourceModel->getExternalId();
        $source->default = $sourceModel->getDefault();
        $source->services = $sourceModel->getServices();
        $source->save();

        return $sourceId;
    }

    private function write_loss_reason(LossReasonModel|null $lossReasonModel): ?int
    {
        if ($lossReasonModel == null)
            return null;
        if (LossReason::find($lossReasonModel->getId()))
            return $lossReasonModel->getId();
        $lossReason = new LossReason();
        $lossReason->id = $lossReasonModel->getId();
        $lossReason->name = $lossReasonModel->getName();
        $lossReason->sort = $lossReasonModel->getSort();
        $lossReason->updated_at = $lossReasonModel->getUpdatedAt();
        $lossReason->created_at = $lossReasonModel->getCreatedAt();
        $lossReason->save();
        return $lossReasonModel->getId();
    }



    private function write_company_by_id( int $id): ?int
    {
        $companyModel = $this->apiClient->companies()->getOne($id, [CompanyModel::CUSTOMERS]);

        $companyId = $companyModel->getId();

        if (Company::find($companyId))
            return $companyId;
        $company = new Company();

        $company->id = $companyModel->getId();
        $company->name = $companyModel->getName();
        $company->group_id = $companyModel->getGroupId();
        $company->responsible_user_id = $companyModel->getResponsibleUserId();
        $company->created_by = $companyModel->getUpdatedBy();
        $company->updated_by = $companyModel->getCreatedBy();
        $company->created_at = $companyModel->getCreatedAt();
        $company->updated_at = $companyModel->getUpdatedAt();
        $company->account_id = $companyModel->getAccountId();
        $company->tags = json_encode($this->write_tags($companyModel->getTags()));
        $company->contacts = json_encode($this->write_contacts($companyModel->getContacts()));
        $company->customers = json_encode($this->write_customers($companyModel->getCustomers()));
        Debugbar::warning($this->json_encode_private($companyModel));
        $company->save();
        return $companyId;
    }

    private function write_contact_by_id(int $id)
    {
        $contactModel = $this->apiClient->contacts()->getOne($id, [ContactModel::CUSTOMERS]);

        $contactId = $contactModel->getId();

        if (Contact::find($contactId))
            return $contactId;
        $contact = new Contact;
        $contact->id = $contactModel->getId();
        $contact->name = $contactModel->getName();
        $contact->first_name = $contactModel->getFirstName();
        $contact->last_name = $contactModel->getLastName();
        $contact->created_by = $contactModel->getCreatedBy();
        $contact->updated_by = $contactModel->getUpdatedBy();
        $contact->company_id = $this->write_company_by_id($contactModel->getCompany()->getId());
        $contact->catalog_elements = $contactModel?->getCatalogElementsLinks()?->jsonSerialize();
        $contact->customers = json_encode($this->write_customers($contactModel->getCustomers()));
        Debugbar::warning($this->json_encode_private($contactModel));

        $contact->save();
        return $contactId;
    }

    private function write_contacts(ContactsCollection|null $contacts): ?array
    {
        if ($contacts == null)
            return null;

        $contacts_ids = [];
        for ($j = 0; $j < $contacts->count(); $j++){

            if($contacts[$j] == null)
                continue;

            $contacts_ids[] = $contacts[$j]->getId();
            if (Contact::find($contacts[$j]->getId()))
                continue;
            $this->write_contact_by_id($contacts[$j]->getId());
        }
        return $contacts_ids;
    }


    private function write_customer_by_id(int $id): ?int
    {
        $customerModel = $this->apiClient->customers()->getOne($id, [CustomerModel::CONTACTS, CustomerModel::COMPANIES]);

        $customerId = $customerModel->getId();

        if (Customer::find($customerId))
            return $customerId;
        $customer = new Customer();

        $customer->id = $customerModel->getId();
        $customer->name = $customerModel->getname();
        $customer->responsible_user_id = $customerModel->getResponsibleUserId();
        $customer->created_by = $customerModel->getCreatedBy();
        $customer->updated_by = $customerModel->getUpdatedBy();
        $customer->created_at = $customerModel->getCreatedAt();
        $customer->updated_at = $customerModel->getUpdatedAt();
        $customer->closest_task_at = $customerModel->getClosestTaskAt();
        $customer->is_deleted = $customerModel->getIsDeleted();
        $customer->ltv = $customerModel->getLtv();
        $customer->purchases_count = $customerModel->getPurchasesCount();
        $customer->average_check = $customerModel->getAverageCheck();
        $customer->account_id = $customerModel->getAccountId();
        $customer->contacts = $customerModel->getContacts();
        $customer->catalog_elements = $customerModel->getCatalogElementsLinks();
        $customer->company_id = $customerModel->getCompany()->getId();
        $customer->tags = $this->write_tags($customerModel->getTags());
        $customer->segments = json_encode($customerModel->getSegments());

        $customer->save();
        return $customerId;
    }

    private function write_customers(CustomersCollection|null $customers): ?array
    {
        if ($customers == null)
            return null;
        $customers_ids = [];
        for ($j = 0; $j < $customers->count(); $j++){

            if($customers[$j] == null)
                continue;

            $customers_ids[] = $customers[$j]->getId();

            if (Customer::find($customers[$j]->getId()))
                continue;
            $this->write_customer_by_id($customers[$j]->getId());
        }
        return $customers_ids;
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    public function __invoke(Request $request): string
    {

        $raw_json = '{"accessToken":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6Ijc0ZDAyZDFjNDdlMzlhNzY4ZjExYjk1ZTI1NWFiNzczZTM5ZjVkNDM1OWM2NDY1Mzk3MzlkNWUxMDRkYTdkYzI0ZDlmNDg1MzVkN2UwOTQwIn0.eyJhdWQiOiI2ZTg0ZThlOS04Y2JmLTQzNmItOTQwMS1jNmExZWM5MDNhNTYiLCJqdGkiOiI3NGQwMmQxYzQ3ZTM5YTc2OGYxMWI5NWUyNTVhYjc3M2UzOWY1ZDQzNTljNjQ2NTM5NzM5ZDVlMTA0ZGE3ZGMyNGQ5ZjQ4NTM1ZDdlMDk0MCIsImlhdCI6MTY2NzQwODA5NSwibmJmIjoxNjY3NDA4MDk1LCJleHAiOjE2Njc0OTQ0OTUsInN1YiI6Ijg4MDI5MTYiLCJhY2NvdW50X2lkIjozMDU5MTM3MCwiYmFzZV9kb21haW4iOiJhbW9jcm0ucnUiLCJzY29wZXMiOlsicHVzaF9ub3RpZmljYXRpb25zIiwiZmlsZXMiLCJjcm0iLCJmaWxlc19kZWxldGUiLCJub3RpZmljYXRpb25zIl19.MrVS1Fs1F2euZV0LhBgpJTWG8FOirl0rl56EM-lhmNLB8fzTz-OOg2AREqmaJimha5tf0g_Bo0Fiue60BCrucnXB2PMQqAT2utRoLQEwdKmigv9xsxaR292Kc-BdMSXE6J9G09equLaAVaDM3a-reUpskrZ2vcNPc9yZ_PFeQuBPEKMQp6StfgADHIXBtYfk04SsWyWnR4ZDfIHd5RfFOS2LzLgDNaTXYhUwjukmsjl_eSVvw_waRfLzCEXSiJWGP0IVtyHVQwyY2apVf7HKTTLPTZfv6dAswovH2wY8d71IKfwPR0RFdCeBGvZMns_z66Ib1NpJaiGXHmKDGfipuA","refreshToken":"def5020093882ff710a8b1d57527069e4e9e292c2cd18369e750d7d1702b66959dc5941d2970fd390e84b358b1333433027801093d62ad0047dca4216bca3bdc41adeced1098cf6026cf23fadb9581dc0ce8ed4e98bfd0ae9736c732cfc2bc036b28ec4890ee7d1d3fc91e54f202d956ea79c34848f426021cc1ab79115484a6628ca9ebde12b924e64d079f733f6c4dcd7dc0e0e17b17c3c5a6070fccecd8d9cba1789deebda1a6d83ce6598af3eb0b54389c0157acc11f6e17d4a9ac8cef85b8f3a7732a1c95bded15d89de06b22e973dc917b6e7b05cfa8b2ddb8e8a48adc86ac80d0b168398033501c00b20f5307ab1fb1ea3f459993fabf4a22608b06fbd0423bf98654b1db00c422c465ac124c383b52bd5ebfef4208952124604de4c22bda6ba3cf98744806ea3da0d950e23673c4a8651463f76125e99eb460afa23fb42d907ffb9cf3b47dfffe50895d066fa1605efa2d132be9cd330521715e5ab052839540da603dc0bd8e7546aa763c8a2b644b874bfd19794c99effbaddb84df66aa95dbdaad59d8d2565ba743cbe356fe2c43c2ba161b1715ea7c9c1c0a6af8bc1f31915e0af5f5bbd2e13d17067fe6bb47e8196e9bd19df0824bd715a8b4332add80dcb27d0ce4017f09b17b9073df5d73a6359f9e64919e0d35e1604fb2e07a","expires":1667494495,"baseDomain":"andrushachel.amocrm.ru"}';
        $test_json = json_decode($raw_json);
        $this->apiClient = new \AmoCRM\Client\AmoCRMApiClient('6e84e8e9-8cbf-436b-9401-c6a1ec903a56', 'OSBJFYKyxhruHucuz62Op3ZA5XYeFKLSqAZdXuDQXw9JDGKO7hk22IYZKDrQ2tRt', "https://sky-e.ru/secret");
        $this->apiClient->setAccountBaseDomain('andrushachel.amocrm.ru')->setAccessToken($this->getToken($raw_json))->onAccessTokenRefresh(
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
            $leadsService = $this->apiClient->leads();

            $leadsCollection = $leadsService->get(null, [LeadModel::CONTACTS, LeadModel::SOURCE_ID, LeadModel::CATALOG_ELEMENTS, LeadModel::LOSS_REASON]);
            $count = $leadsCollection->count();
            for ($i = 0; $i < $count; $i += 1) {
                $l = $leadsCollection[$i];
                if ($l != null) {
                    $lead = new Lead();
                    if (Lead::find($l->getId()))
                        Lead::find($l->getId())->delete();
                    $lead->id = $l->getId();

                    $lead->name = $l->getName();

                    $lead->price = $l->getPrice();

                    $lead->created_at = $l->getCreatedAt();

                    $lead->updated_at = $l->getUpdatedAt();

                    $lead->created_by = $l->getCreatedBy();

                    $lead->updated_by = $l->getUpdatedBy();

                    $lead->responsible_user_id = $this->write_user_by_id($l->getResponsibleUserId());

                    $lead->group_id = $this->write_group_by_id($l->getGroupId());

                    $lead->status_id = $this->write_status_by_id($l->getStatusId(), $l->getPipelineId());

                    $lead->pipeline_id = $this->write_pipeline_by_id($l->getPipelineId());

                    $lead->source_id = $this->write_source_by_id( $l->getSourceExternalId());

                    $lead->tags = json_encode($this->write_tags($l->getTags()));

                    $lead->loss_reason_id = $this->write_loss_reason($l->getLossReason());

                    $lead->company_id = $this->write_company_by_id($l->getCompany()->getId());

                    $lead->catalog_elements = json_encode($l->getCatalogElementsLinks()?->jsonSerialize()) ?? null;

                    $lead->contacts = json_encode($this->write_contacts($l->getContacts()));

                    $lead->save();
                }
            }
        }

         catch (AmoCRMApiException $e) {
            return (string) $e;
        }

        return Group::All();

    }


}
