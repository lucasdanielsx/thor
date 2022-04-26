<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Extrato
 */
class Statement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'wallet_id',
        'transaction_id',
        'value',
        'status',
        'type',
        'old_balance',
        'new_balance'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string',
        'wallet_id' => 'string',
        'transaction_id' => 'string',
    ];

    /**
     * Get wallet associated with statement.
     */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Get transactions associated with statement.
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
