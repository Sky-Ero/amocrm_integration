<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $name
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property integer $account_id
 * @property mixed $custom_fields_values
 * @property boolean $is_deleted
 * @property integer $quantity
 */
class Catalog extends Model
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
    protected $fillable = ['name', 'created_by', 'updated_by', 'created_at', 'updated_at', 'account_id', 'custom_fields_values', 'is_deleted', 'quantity'];
}
