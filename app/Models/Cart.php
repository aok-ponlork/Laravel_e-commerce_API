<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'carts';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id', 'product_id', 'quantity', 'product_price'
    ];

     // Relationship with User
     public function user()
     {
         return $this->belongsTo(User::class, 'user_id');
     }
 
     // Relationship with Product
     public function product()
     {
         return $this->belongsTo(Product::class, 'product_id');
     }
}
