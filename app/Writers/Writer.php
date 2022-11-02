<?php

namespace App\Writers;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\BaseApiCollection;
use App\Models\Group;

abstract class Writer
{

    protected AmoCRMApiClient $apiClient;

    public function __construct(AmoCRMApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public abstract function write_by_id(int|null $id);
    public abstract function write_collection(BaseApiCollection|null $collection);
}
