<?php

namespace App\Writers;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\BaseApiCollection;
use AmoCRM\Models\CompanyModel;
use App\Models\Company;

class CompanyWriter extends Writer
{

    private CustomerWriter $customer;
    protected ContactWriter $contact;
    private GroupWriter $group;
    private TagWriter $tag;

    public function __construct(AmoCRMApiClient $apiClient, CustomerWriter $customerWriter, TagWriter $tagWriter, ContactWriter $contactWriter, GroupWriter $groupWriter)
    {
        $this->group = $groupWriter;
        $this->tag = $tagWriter;
        $this->contact = $contactWriter;
        $this->customer = $customerWriter;
        parent::__construct($apiClient);
    }

    public function write_by_id(?int $id)
    {
        $companyModel = $this->apiClient->companies()->getOne($id, [CompanyModel::CUSTOMERS]);

        $companyId = $companyModel->getId();

        if (Company::find($companyId))
            return $companyId;
        $company = new Company();

        $company->id = $companyModel->getId();
        $company->name = $companyModel->getName();
        $company->group_id = $this->group->write_by_id($companyModel->getGroupId());
        $company->responsible_user_id = $companyModel->getResponsibleUserId();
        $company->created_by = $companyModel->getUpdatedBy();
        $company->updated_by = $companyModel->getCreatedBy();
        $company->created_at = $companyModel->getCreatedAt();
        $company->updated_at = $companyModel->getUpdatedAt();
        $company->account_id = $companyModel->getAccountId();
        $company->tags = json_encode($this->tag->write_collection($companyModel->getTags()));
        $company->contacts = json_encode($this->contact->write_collection($companyModel->getContacts()));
        $company->customers = json_encode($this->customer->write_collection($companyModel->getCustomers()));
        $company->save();
        return $companyId;
    }

    public function write_collection(?BaseApiCollection $collection)
    {
        // TODO: Implement write_collection() method.
    }
}
