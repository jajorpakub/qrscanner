<?php

namespace App\Models;

class TechnicalRecord
{
    public int $id;
    public int $deviceId;
    public int $userId;
    public string $recordDate;
    public string $recordType; // 'inspection', 'maintenance', 'repair', 'testing'
    public string $description;
    public ?string $technician;
    public ?string $notes;
    public ?\DateTime $createdAt;

    public function __construct(
        int $deviceId,
        int $userId,
        string $recordDate,
        string $recordType,
        string $description,
        ?string $technician = null,
        ?string $notes = null
    ) {
        $this->deviceId = $deviceId;
        $this->userId = $userId;
        $this->recordDate = $recordDate;
        $this->recordType = $recordType;
        $this->description = $description;
        $this->technician = $technician;
        $this->notes = $notes;
        $this->createdAt = new \DateTime();
    }
}
