<?php

namespace App\Writers;

use AmoCRM\Collections\BaseApiCollection;
use App\Models\Group;

class GroupWriter extends Writer
{
    public function write_by_id(?int $id): ?int
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

    public function write_collection(?BaseApiCollection $collection)
    {
        // TODO: Implement write() method.
    }
}
