<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'user_id',
        'invoice_date',
        'client_id',
        'client_address',
        'client_name',
        'client_email',
        'client_phone',
        'client_alternate_phone',
        'total_amount',
        'delivery_amount',
        'additional_information',
        'status'
    ];

    protected $casts = [
        // 'order_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',

    ];

       // relations between Order & Order Items
   public function invoice_items(): HasMany
   {
       return $this->hasMany(InvoiceItem::class);
   }

   // inverse relationship between user - orders
   public function user(): BelongsTo
   {
       return $this->belongsTo(User::class);
   }
}
