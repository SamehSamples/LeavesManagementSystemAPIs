<?php

namespace App\Http\Resources;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeLeaveRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'request_id'=>$this->employeeLeaveRequest->id,
            'duration'=>$this->employeeLeaveRequest->duration,
            'starting_date'=>$this->employeeLeaveRequest->starting_date,
            'ending_date'=>$this->employeeLeaveRequest->ending_date,
            'status'=>$this->employeeLeaveRequest->status,
            'requested_by'=> is_null($this->employeeLeaveRequest->requested_by) ? null : new MiniEmployeeResource(Employee::find($this->employeeLeaveRequest->requested_by)),
            'actioned_by'=> is_null($this->employeeLeaveRequest->actioned_by) ? null : new MiniEmployeeResource(Employee::find($this->employeeLeaveRequest->actioned_by)),
            'created_at'=>Carbon::parse($this->employeeLeaveRequest->created_at)->toDateTimeString(),
            'updated_at'=>Carbon::parse($this->employeeLeaveRequest->updated_at)->toDateTimeString(),
        ];
    }
}
