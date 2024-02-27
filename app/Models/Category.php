<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable =[
        'name',
    ];

    protected $hidden = [
        'category_product',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class)->withTimestamps()->as('category_product')->using(CategoryProduct::class);
    }
}
