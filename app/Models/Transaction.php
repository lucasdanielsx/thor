<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
      protected $fillable = [
        'id',
        'value',
        'status',
        'payload'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string'
    ];

    /**
     * Get events associated with transaction.
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get the statements associated with the transaction.
     */
    public function statements()
    {
        return $this->hasMany(Statement::class);
    }
}
