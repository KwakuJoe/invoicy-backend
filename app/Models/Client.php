<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "user_id",
        "phone",
        "email",
        'alternate_phone',
        'address',
    ];

    protected $casts = [
        // 'order_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

        // inverse of relation user - clients
        public function user(): BelongsTo
        {
            return $this->belongsTo(User::class);
        }
}
