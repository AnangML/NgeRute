<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTOs\JourneyRequestDto;
use Illuminate\Foundation\Http\FormRequest;

final class PlanJourneyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'origin_id' => ['required', 'integer', 'exists:stops,id'],
            'destination_id' => ['required', 'integer', 'exists:stops,id', 'different:origin_id'],
            'criteria' => ['nullable', 'string', 'in:fastest,cheapest,least_walking,fewest_transfers'],
        ];
    }

    public function toDto(): JourneyRequestDto
    {
        return new JourneyRequestDto(
            originStopId: (int) $this->validated('origin_id'),
            destinationStopId: (int) $this->validated('destination_id'),
            preferredCriteria: $this->validated('criteria')
        );
    }
}
