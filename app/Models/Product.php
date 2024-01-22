<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $primaryKey = 'product_id';

    protected $fillable =[
        'product_name',
        'price',
        'cost',
        'stock_qty',
        'description',
        'image',
        'category_id'
    ];
    public function category(){
        return $this->belongsTo(Category::class, 'category_id');
    }
}
