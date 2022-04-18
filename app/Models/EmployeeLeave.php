<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EmployeeLeave extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_id',
        'duration',
        'starting_date',
        'ending_date',
        'pay_percentage',
        'status',
        'requested_by',
        'actioned_by',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
    ];

    public function requestedBy(): HasOne
    {
        return $this->hasOne(Employee::class,'id','requested_by');
    }

    public function actionedBy(): HasOne
    {
        return $this->hasOne(Employee::class,'id','actioned_by');
    }
}
