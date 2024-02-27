<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResetPassword extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'pin_code',
        'email',
        'number_of_send',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
