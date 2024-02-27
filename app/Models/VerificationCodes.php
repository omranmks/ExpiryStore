<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationCodes extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pin_code',
    ];
    function user(){
        return $this->belongsTo(User::class);
    }
}
