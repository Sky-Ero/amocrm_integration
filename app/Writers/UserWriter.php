<?php

namespace App\Writers;

use AmoCRM\Collections\BaseApiCollection;
use AmoCRM\Models\UserModel;
use App\Models\User;

class UserWriter extends Writer
{
    private function write_user_by_model(UserModel $userModel): ?int
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

    public function write_by_id(?int $id)
    {
        return $this->write_user_by_model($this->apiClient->users()->getOne($id));
    }

    public function write_collection(?BaseApiCollection $collection)
    {
        // TODO: Implement write_collection() method.
    }
}
