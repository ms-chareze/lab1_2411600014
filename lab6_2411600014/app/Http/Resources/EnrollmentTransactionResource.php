<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'type'             => $this->type,
            'type_label'       => $this->type_label,
            'type_badge'       => $this->type_badge,
            'quantity'         => $this->quantity,
            'signed_quantity'  => $this->signed_quantity,
            'reference_number' => $this->reference_number,
            'student'          => $this->whenLoaded('student', fn () => [
                'id'         => $this->student->id,
                'student_id' => $this->student->student_id,
                'name'       => $this->student->name,
                'url'        => route('students.show', $this->student->id),
            ]),
            'user'             => $this->whenLoaded('user', fn () => $this->user?->name),
            'created_at'       => $this->created_at?->toIso8601String(),
            'created_at_human' => $this->created_at?->format('M d, Y h:i A'),
            'url'              => route('transactions.show', $this->id),
        ];
    }
}
