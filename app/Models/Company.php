<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property string $name
 * @property integer $group_id
 * @property integer $responsible_user_id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $account_id
 * @property mixed $tags
 * @property mixed $contacts
 * @property mixed $customers
 * @property integer $company_id
 */
class Company extends Model
{
    /**
     * The "type" of the auto-incrementing ID.
     * 
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['created_at', 'updated_at', 'name', 'group_id', 'responsible_user_id', 'created_by', 'updated_by', 'account_id', 'tags', 'contacts', 'customers', 'company_id'];
}
