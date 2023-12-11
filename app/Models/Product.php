<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        "image",
        "user_id",
        "name",
        'description',
        'price',
    ];

    protected $casts = [
        // 'order_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // inverse of relation user -products
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

       // inverse relationshp between invoice_item and product
       public function invoice_item(): BelongsTo
       {
           return $this->belongsTo(InvoiceItem::class);
       }
}
