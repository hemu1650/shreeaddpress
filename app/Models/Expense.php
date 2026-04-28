<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $table = 'expenses';

    protected $fillable = [
        'staff_id',
        'category_id',
        'order_id',
        'expense_name',
        'amount',
        'expense_date',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date'
    ];
    

    /*
     * |--------------------------------------------------------------------------
     * | Relationships
     * |--------------------------------------------------------------------------
     */

    // Expense belongs to Staff
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    // Expense belongs to Order (optional)
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /*
     * |--------------------------------------------------------------------------
     * | Scopes (Filters)
     * |--------------------------------------------------------------------------
     */

    // Filter by staff
    public function scopeByStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    // Filter by date
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('expense_date', $date);
    }

    // Filter by month
    public function scopeByMonth($query, $month)
    {
        return $query->whereMonth('expense_date', $month);
    }

    /*
     * |--------------------------------------------------------------------------
     * | Accessors
     * |--------------------------------------------------------------------------
     */

    // Format amount
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    /*
     * |--------------------------------------------------------------------------
     * | Boot (Auto Data Handling)
     * |--------------------------------------------------------------------------
     */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($expense) {
            // अगर expense_date नहीं आया तो auto set करो
            if (!$expense->expense_date) {
                $expense->expense_date = now();
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class);
    }
}
