<?php

namespace App\Writers;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\BaseApiCollection;
use AmoCRM\Models\ContactModel;
use App\Models\Contact;

class ContactWriter extends Writer
{

    private CustomerWriter $customer;

    public function __construct(AmoCRMApiClient $apiClient, CustomerWriter $customerWriter)
    {
        $this->customer = $customerWriter;
        parent::__construct($apiClient);
    }

    public function write_by_id(?int $id)
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
        $contact->company_id = $this->write_by_id($contactModel->getCompany()->getId());
        $contact->catalog_elements = $contactModel?->getCatalogElementsLinks()?->jsonSerialize();
        $contact->customers = json_encode($this->customer->write_collection($contactModel->getCustomers()));
        $contact->save();
        return $contactId;
    }

    public function write_collection(?BaseApiCollection $collection): ?array
    {
        if ($collection == null)
            return null;

        $contacts_ids = [];
        for ($j = 0; $j < $collection->count(); $j++){

            if($collection[$j] == null)
                continue;

            $contacts_ids[] = $collection[$j]->getId();
            if (Contact::find($collection[$j]->getId()))
                continue;
            $this->write_by_id($collection[$j]->getId());
        }
        return $contacts_ids;
    }
}
