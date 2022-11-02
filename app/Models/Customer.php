<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $name
 * @property boolean $is_main
 * @property integer $sort
 * @property integer $responsible_user_id
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property string $closest_task_at
 * @property integer $ltv
 * @property boolean $is_deleted
 * @property integer $purchases_count
 * @property integer $average_check
 * @property integer $account_id
 * @property mixed $catalog_elements
 * @property mixed $company_id
 * @property mixed $tags
 * @property mixed $segments
 * @property mixed $contacts
 */
class Customer extends Model
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
    protected $fillable = ['name', 'is_main', 'sort', 'responsible_user_id', 'created_by', 'updated_by', 'created_at', 'updated_at', 'closest_task_at', 'ltv', 'is_deleted', 'purchases_count', 'average_check', 'account_id', 'catalog_elements', 'company_id', 'tags', 'segments', 'contacts'];
}
