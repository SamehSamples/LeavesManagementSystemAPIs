<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'country_code',
        'mobile',
        'job_title',
        'country_id',
        'gender',
        'is_active',
        'avatar',
        'joining_date',
        'last_working_date',
        'department_id',
        'reporting_to',
        'salary',
        'leaves_history',
        'self_service',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function department(): HasOne
    {
        return $this->hasOne(Department::class,'id','department_id');
    }

    public function manager(): HasOne
    {
        return $this->hasOne(Employee::class,'id','reporting_to');
    }

    public function leaves(): BelongsToMany
    {
        return $this->belongsToMany(Leave::class,'employee_leaves')
            ->as('employeeLeaveRequest')
            ->withPivot(
                'id',
                'duration',
                'starting_date',
                'ending_date',
                'pay_percentage',
                'status',
                'requested_by',
                'actioned_by',
                'created_at',
                'updated_at'
            );
    }
}
