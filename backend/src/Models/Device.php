<?php

namespace App\Models;

class Device
{
    public int $id;
    public string $name;
    public string $type;
    public string $location;
    public string $manufacturer;
    public string $serialNumber;
    public string $installDate;
    public ?string $qrCode;
    public int $userId;
    public ?\DateTime $createdAt;
    public ?\DateTime $updatedAt;

    public function __construct(
        string $name,
        string $type,
        string $location,
        string $manufacturer,
        string $serialNumber,
        string $installDate,
        int $userId
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->location = $location;
        $this->manufacturer = $manufacturer;
        $this->serialNumber = $serialNumber;
        $this->installDate = $installDate;
        $this->userId = $userId;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }
}
