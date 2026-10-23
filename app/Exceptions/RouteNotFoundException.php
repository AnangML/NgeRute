<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class RouteNotFoundException extends RuntimeException
{
    public static function between(string $originName, string $destinationName): self
    {
        return new self("Tidak ditemukan alternatif rute yang menghubungkan '{$originName}' dan '{$destinationName}' dalam dataset saat ini.");
    }
}
