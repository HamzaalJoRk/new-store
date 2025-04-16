<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\EmployeeTrait;
class Order extends Model
{
    use HasFactory,EmployeeTrait;
    protected $guarded=[];
    public function scopeMyOrders($query)
    {
        return $query->where('user_id',  auth()->id());
    }
    public function scopePending($query)
    {
        return $query->where('status',  'pending');
    }
    public function user(){
        return $this->belongsTo(Admin::class);
    }
    public function user_c(){
        return $this->belongsTo(User::class,'user_id');
    }
    public function game(){
        return $this->belongsTo(Game::class);
    }
    public function package(){
        return $this->belongsTo(Package::class);
    }
    public function status()
    {
        return $this->status ;
    }
     // canceled_by
     public function canceledBy()
     {
         return $this->belongsTo(Admin::class, 'canceled_by');
     }
}
