<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $name
 * @property integer $pipeline_id
 * @property integer $external_id
 * @property boolean $default
 * @property mixed $services
 */
class Source extends Model
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
    protected $fillable = ['name', 'pipeline_id', 'external_id', 'default', 'services'];
}
