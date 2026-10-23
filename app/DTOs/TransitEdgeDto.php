<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class TransitEdgeDto
{
    /**
     * @param list<array{0: float, 1: float}>|null $pathCoordinates
     */
    public function __construct(
        public int $fromStopId,
        public int $toStopId,
        public int $distanceMeters,
        public int $durationSeconds,
        public string $edgeType, // 'transit' | 'transfer'
        public ?int $routeId = null,
        public ?string $routeCode = null,
        public ?string $routeName = null,
        public ?string $headsign = null,
        public ?string $operatorCode = null,
        public ?string $operatorName = null,
        public ?string $mode = null,
        public ?string $colorHex = null,
        public ?string $transferType = null,
        public ?string $transferNotes = null,
        public int $estimatedFare = 0,
        public ?array $pathCoordinates = null,
    ) {}

    public function isTransfer(): bool
    {
        return $this->edgeType === 'transfer';
    }

    public function isTransit(): bool
    {
        return $this->edgeType === 'transit';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'fromStopId' => $this->fromStopId,
            'toStopId' => $this->toStopId,
            'distanceMeters' => $this->distanceMeters,
            'durationSeconds' => $this->durationSeconds,
            'edgeType' => $this->edgeType,
            'routeId' => $this->routeId,
            'routeCode' => $this->routeCode,
            'routeName' => $this->routeName,
            'headsign' => $this->headsign,
            'operatorCode' => $this->operatorCode,
            'operatorName' => $this->operatorName,
            'mode' => $this->mode,
            'colorHex' => $this->colorHex,
            'transferType' => $this->transferType,
            'transferNotes' => $this->transferNotes,
            'estimatedFare' => $this->estimatedFare,
            'pathCoordinates' => $this->pathCoordinates,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            fromStopId: (int) $data['fromStopId'],
            toStopId: (int) $data['toStopId'],
            distanceMeters: (int) $data['distanceMeters'],
            durationSeconds: (int) $data['durationSeconds'],
            edgeType: (string) $data['edgeType'],
            routeId: isset($data['routeId']) ? (int) $data['routeId'] : null,
            routeCode: $data['routeCode'] ?? null,
            routeName: $data['routeName'] ?? null,
            headsign: $data['headsign'] ?? null,
            operatorCode: $data['operatorCode'] ?? null,
            operatorName: $data['operatorName'] ?? null,
            mode: $data['mode'] ?? null,
            colorHex: $data['colorHex'] ?? null,
            transferType: $data['transferType'] ?? null,
            transferNotes: $data['transferNotes'] ?? null,
            estimatedFare: (int) ($data['estimatedFare'] ?? 0),
            pathCoordinates: $data['pathCoordinates'] ?? null,
        );
    }
}
