<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeTransactionLog extends Model
{
    protected $fillable = [
        'transaction_type',
        'by_user_id',
        'employee_id',
        'transaction_details',
    ];

    protected $casts = [
        'transaction_details'=>'array',
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
    ];
}
