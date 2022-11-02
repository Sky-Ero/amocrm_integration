<?php

namespace App\Writers;

use AmoCRM\Collections\BaseApiCollection;
use App\Models\LossReason;

class LossReasonWriter extends Writer
{


    public function write_by_id(?int $id)
    {
        if ($id == null)
            return null;
        $lossReasonModel = $this->apiClient->lossReasons()->getOne($id);

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

    public function write_collection(?BaseApiCollection $collection)
    {
        // TODO: Implement write_collection() method.
    }
}
