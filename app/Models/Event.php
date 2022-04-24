<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'transaction_id',
        'type',
        'payload'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string',
        'transaction_id' => 'string',
    ];

    /**
     * Get the transaction associated with the event.
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
