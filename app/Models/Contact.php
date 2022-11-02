<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $created_at
 * @property string $updated_at
 * @property string $name
 * @property string $first_name
 * @property string $last_name
 * @property integer $created_by
 * @property integer $updated_by
 * @property mixed $companies
 * @property mixed $catalog_elements
 * @property mixed $customers
 * @property integer $company_id
 */
class Contact extends Model
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
    protected $fillable = ['created_at', 'updated_at', 'name', 'first_name', 'last_name', 'created_by', 'updated_by', 'companies', 'catalog_elements', 'customers', 'company_id'];
}
