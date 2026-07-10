<?php
// app/Models/ProductStatePrice.php
namespace App\Models;
use Illuminate\Database\Eloquent\Casts\Attribute;

use Illuminate\Database\Eloquent\Model;

class ProductStatePrice extends Model
{
    protected $fillable = ['product_id', 'state_id', 'price'];
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }
    
    public function getFormattedPriceAttribute()
    {
        return '₦' . number_format($this->price, 2);
    }
}
