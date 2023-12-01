<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordResetTokens extends Model
{
    use HasFactory;

    public $fillable = [
        'email',
        'token',
        'created_at'
    ] ;

    // protected $casts = [
    //     'order_date' => 'datetime',
    //     'created_at' => 'datetime',
    //     'updated_at' => 'datetime',
    // ];

    public $table = 'password_reset_tokens';
    public $timestamps = false;

    protected $primaryKey = 'email';
}
