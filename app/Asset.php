<?php
declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    /**
     * Assets table.
     *
     * @var string
     */
    protected $table = 'assets';

    protected $guarded = [];

    protected $hidden = [
        'user_id',
        'created_at',
        'updated_at',
    ];
}
