<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'student_id'        => $this->student_id,
            'name'              => $this->name,
            'course'            => $this->course,
            'gpa'               => $this->gpa,
            'status'            => $this->status,
            'enrolled_units'    => $this->enrolled_units,
            'min_units'         => $this->min_units,
            'load_status'       => $this->load_status,
            'load_status_color' => $this->load_status_color,
            'url'               => route('students.show', $this->id),
        ];
    }
}
