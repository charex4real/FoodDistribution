<?php

namespace App\Models;
use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;
use App\Constants\Status; 
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Stockist extends Model
{
    use GlobalStatus;
    use HasFactory, Notifiable;

     protected $fillable = [
        'user_id', 'business_name', 'state_id', 'business_email', 'business_phone',
        'business_registration_number', 'business_description', 'website',
        'business_hours', 'is_verified', 'is_active', 'verified_at'
    ];

    protected $casts = [
        'business_hours' => 'array',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'verified_at' => 'datetime'
    ];

     public function locations(){
        return $this->hasMany(StockistLocation::class);
    }

   

    public function services(){
        return $this->hasMany(StockistService::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function stockiststore()
    {
        return $this->hasOne(Stockist_store::class, 'user_id');
    }
    public function stockistStores()
    {
        return $this->hasMany(Stockist_store::class);
    }

    public function sktransactions()
    {
        return $this->hasMany(Sktransaction::class)->latest();
    }

    // Accessors
    public function getStoreTypeNameAttribute()
    {
        return $this->store_type == 1 ? 'Mega Store' : 'Mini Store';
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    // Accessor for state name
    public function getStateNameAttribute()
    {
        return $this->state ? $this->state->name : 'Not Specified';
    }

    public function getFormattedWalletAttribute()
    {
        return '₦' . number_format($this->wallet, 2);
    }

    public function getStatusBadgeAttribute()
    {
        if (!$this->is_active) {
            return '<span class="badge badge-danger">Inactive</span>';
        }

        if (!$this->is_verified) {
            return '<span class="badge badge-warning">Pending Verification</span>';
        }

        return '<span class="badge badge-success">Active</span>';
    }




    public function isStockist()
    {
        return !is_null($this->stockist);
    }


    public function primaryLocation()
    {
        return $this->hasOne(StockistLocation::class)->where('is_primary', true);
    }

    public function redemptions()
    {
        return $this->hasMany(InvoiceRedemption::class); 
    }

    
     // SCOPES
    public function scopeActive($query)
    {
        return $query->where('status', Status::ACTIVE);
    }

    // Helper methods
    public function getActiveLocationsAttribute()
    {
        return $this->locations()->where('is_active', true)->get();
    }

    public function getActiveServicesAttribute()
    {
        return $this->services()->where('is_active', true)->get();
    }

    Public function inventory()
    {
        return $this->hasMany(Stockist_store::class);
    }

    public function orders()
    {
        return $this->hasMany(StockistOrder::class);
    }

    public function getLowStockItemsAttribute()
    {
        return $this->inventory()->where('quantity', '<', 20)->get();
    }

    public function getOutOfStockItemsAttribute()
    {
        return $this->inventory()->where('quantity',  0)->get();
    }

    public function getTotalInventoryValueAttribute()
    {
        return $this->inventory()->sum('total_value');
    }

    public function deductFromWallet($amount)
    {
        if ($this->wallet >= $amount) {
            $this->wallet -= $amount;
            $this->save();
            return true;
        }
        return false;
    }

    public function addToWallet($amount)
    {
        $this->wallet += $amount;
        $this->save();
        return true;
    }
   

}
