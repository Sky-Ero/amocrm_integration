<?php

namespace App\Writers;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\BaseApiCollection;
use AmoCRM\Models\Customers\CustomerModel;
use App\Models\Customer;

class CustomerWriter extends Writer
{
    private TagWriter $tag;

    public function __construct(AmoCRMApiClient $apiClient, TagWriter $tagWriter)
    {
        $this->tag = $tagWriter;
        parent::__construct($apiClient);
    }

    public function write_by_id(?int $id)
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
        $customer->tags = $this->tag->write_collection($customerModel->getTags());
        $customer->segments = json_encode($customerModel->getSegments());

        $customer->save();
        return $customerId;
    }

    public function write_collection(?BaseApiCollection $collection)
    {
        if ($collection == null)
            return null;
        $customers_ids = [];
        for ($j = 0; $j < $collection->count(); $j++){

            if($collection[$j] == null)
                continue;

            $customers_ids[] = $collection[$j]->getId();

            if (Customer::find($collection[$j]->getId()))
                continue;
            $this->write_by_id($collection[$j]->getId());
        }
        return $customers_ids;
    }
}
