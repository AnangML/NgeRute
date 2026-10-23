<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class JourneyRequestDto
{
    public function __construct(
        public int $originStopId,
        public int $destinationStopId,
        public ?string $preferredCriteria = null,
    ) {}
}
