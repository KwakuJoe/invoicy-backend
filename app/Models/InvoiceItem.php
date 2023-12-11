<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'product_id',
        'quantity',
        'total',
        'price'
    ];

    protected $casts = [
        // 'invoice_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',

    ];

        // inverse of relation invoice - invoice_items
        public function invoice(): BelongsTo
        {
            return $this->belongsTo(Invoice::class);
        }


        // relationship between order_item and products

        public function product(): BelongsTo
        {
            return $this->belongsTo(Product::class);
        }
}
