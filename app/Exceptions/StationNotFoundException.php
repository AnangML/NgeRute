<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class StationNotFoundException extends RuntimeException
{
    public static function forId(int $id): self
    {
        return new self("Stasiun atau halte dengan ID {$id} tidak ditemukan.");
    }
}
