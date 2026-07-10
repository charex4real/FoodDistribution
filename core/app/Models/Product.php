<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    use GlobalStatus;

    protected $casts = [
        'specifications'    => 'array',
        'meta_keyword'      => 'array',
    ];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function productstate()
    {
        return $this->hasMany(ProductStatePrice::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    public function statusFeature(): Attribute
    {
        return new Attribute(function () {
            $html = '';
            if ($this->is_featured == Status::ENABLE) {
                $html = '<span class="badge badge--success">' . trans('Featured') . '</span>';
            } else {
                $html = '<span class="badge badge--warning">' . trans('UnFeatured') . '</span>';
            }
            return $html;
        });
    }

    public function scopeHasCategory($q)
    {
        return $q->whereHas('category', function ($q) {
            $q->active();
        });
    }

    public function getFormattedPriceAttribute()
    {
        return '₦' . number_format($this->price, 2);
    }


     public function inventory()
    {
        return $this->hasMany(StockistInventory::class, 'product_id');
    }

    public function orderItems()
    {
        return $this->hasMany(StockistOrderItem::class, 'product_id');
    }

    public function getIsAvailableAttribute()
    {
        return $this->status && $this->quantity > 0;
    }

    public function statePrices()
    {
        return $this->hasMany(ProductStatePrice::class);
    }
    
    public function getPriceForState($stateId)
    {
        $statePrice = $this->statePrices()->where('state_id', $stateId)->first();
        return $statePrice ? $statePrice->price : $this->price; // fallback to default price
    }
}
