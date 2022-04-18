<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'name' ,
        'pay_percentage' ,
        'default_block_duration_in_days' ,
        'days_allowed_after' ,
        'calculation_period' ,
        'allowed_blocks_per_period' ,
        'leave_allowed_after' ,
        'dividable'  ,
        'balance_is_accumulated'  ,
        'grade_sensitive'  ,
        'gender_strict' ,
        'is_active' ,
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
    ];

    public function hasBalance():bool
    {
        return !is_null($this->allowed_blocks_per_period);
    }

    public function accumulatable():bool
    {
        return $this->balance_is_accumulated;
    }

    public function isDividable():bool
    {
        return $this->dividable;
    }

    public function isGenderStrict():?bool
    {
        return $this->gender_strict;
    }

    public function isGradeSensitive():bool
    {
        return $this->grade_sensitive;
    }

    public function isActive():bool
    {
        return $this->is_active;
    }

    public function duration():int{
        return $this->default_block_duration_in_days;
    }
}
