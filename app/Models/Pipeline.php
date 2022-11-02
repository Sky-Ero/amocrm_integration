<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $name
 * @property boolean $is_main
 * @property integer $sort
 * @property integer $account_id
 */
class Pipeline extends Model
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
    protected $fillable = ['name', 'is_main', 'sort', 'account_id'];
    public $timestamps = false;
}
