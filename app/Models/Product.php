<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'store_id',
        'image',
        'quantity',
        'price',
        'price_optioin',
        'description',
        'current_price',
        'expiry_date',
    ];

    protected $hidden = [
        'category_product',
        'price_optioin',
        'price',
        'image',
        'store_id',
        'created_at',
        'updated_at',
    ];

    public function scopeFilter($query, $filterSearch = null,
     array $filterCategories = null,
      $filterDate = null,
      $filterPrice = null)
    {
        $query->when(
            $filterSearch ?? false,
            fn ($query, $search) =>
            $query->where(fn ($query) =>
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%'))
        );

        $query->when(
            $filterCategories ?? false,
            fn ($query, $categories) =>
            $query->where(fn ($query) =>
            $query->whereHas('categories', fn ($query) =>
            $query->whereIn('categories.id', $categories)))
        );

        $query->when(
            $filterDate ?? false,
            fn($query, $date) =>
            $query->where(fn($query) =>
            $query->whereDate('expiry_date', '>=', $date))
        );

        $query->when(
            $filterPrice ?? false,
            fn($query, $price) =>
            $query->where(fn($query) =>
            $query->where('current_price', '<=', $price))
        );
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class)->as('category_product')->using(CategoryProduct::class);
    }
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function getExpiryDateAttribute($date)
    {
        $time = new DateTime($date);
        return  $time->format('Y-m-d');
    }
}
