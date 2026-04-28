<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAttendance extends Model
{
    protected $table = 'staff_attendance';
    protected $fillable = ['staff_id', 'date', 'status'];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
