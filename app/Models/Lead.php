<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $name
 * @property integer $price
 * @property integer $responsible_user_id
 * @property integer $group_id
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property integer $account_id
 * @property integer $pipeline_id
 * @property integer $status_id
 * @property integer $source_id
 * @property integer $loss_reason_id
 * @property mixed $tags
 * @property integer $company_id
 * @property mixed $catalog_elements
 * @property mixed $contacts
 */
class Lead extends Model
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
    protected $fillable = ['name', 'price', 'responsible_user_id', 'group_id', 'created_by', 'updated_by', 'created_at', 'updated_at', 'account_id', 'pipeline_id', 'status_id', 'source_id', 'loss_reason_id', 'tags', 'company_id', 'catalog_elements', 'contacts'];
}
