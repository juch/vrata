<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPerimeters extends Model
{
    // use SoftDeletes;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_perimeters';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'uuid';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'perimeters' => 'array',
    ];

    /**
     * Undocumented function
     *
     * @param string $provider
     * @return boolean
     */
    public function isGranted(string $provider): bool
    {
        if (!empty($this->perimeters['providers'])) {
            return in_array($provider, $this->perimeters['providers']);
        }

        return false;
    }
}