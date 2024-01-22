<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'task_name', 'description', 'priority'
    ];

    // Define the relationship between tasks and users this mean one task is belong to one user that why we create the method sigularly.
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
