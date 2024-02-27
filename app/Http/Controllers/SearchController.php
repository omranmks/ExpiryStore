<?php

namespace App\Http\Controllers;

use App\Models\Product;

class SearchController extends Controller
{
    public function GetSearch()
    {
        $pro = Product::filter(
            request()->search,
            request()->categories,
            request()->date,
            request()->price
        )
            ->when(request()->sortDESC ?? false, fn ($query) => $query->orderBy('created_at', 'DESC'))
            ->when(request()->sortASC ?? false, fn ($query) => $query->orderBy('created_at', 'ASC'))
            ->when(request()->sortPriceDESC ?? false, fn ($query) => $query->orderBy('current_price', 'DESC'))
            ->when(request()->sortPriceASC ?? false, fn ($query) => $query->orderBy('current_price', 'ASC'))
            ->when(request()->sortExpiryDateDESC ?? false, fn ($query) => $query->orderBy('expiry_date', 'DESC'))
            ->when(request()->sortExpiryDateASC ?? false, fn ($query) => $query->orderBy('expiry_date', 'ASC'))
            ->paginate(35);
        
        $attrebutes = [
            'status' => 'success'
        ];

        $attrebutes = array_merge($attrebutes, $pro->toArray());

        return response($attrebutes, 200);
    }
}
