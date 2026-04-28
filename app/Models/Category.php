<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name'];

    // Relation with Expense
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}