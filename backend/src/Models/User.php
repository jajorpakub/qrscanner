<?php

namespace App\Models;

class User
{
    public int $id;
    public string $email;
    public string $password;
    public string $name;
    public string $role; // 'owner', 'technician', 'viewer'
    public ?\DateTime $createdAt;
    public ?\DateTime $updatedAt;

    public function __construct(
        string $email,
        string $password,
        string $name,
        string $role = 'viewer'
    ) {
        $this->email = $email;
        $this->password = password_hash($password, PASSWORD_BCRYPT);
        $this->name = $name;
        $this->role = $role;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }
}
