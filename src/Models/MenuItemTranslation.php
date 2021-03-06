<?php

namespace Laradium\Laradium\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItemTranslation extends Model
{

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'url',
        'locale',
    ];
}
