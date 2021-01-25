<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2018  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace Lidere\Modules\Tasks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Task
 *
 * @package Lidere\Modules
 * @subpackage Task\Models
 * @author Ramon Barros
 */
class Task extends Model
{
    public $table = 'ttasks';

    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'minute',
        'hour',
        'day',
        'month',
        'weekday',
        'commonOptions',
        'job',
        'description',
        'running_at'
    ];
}
