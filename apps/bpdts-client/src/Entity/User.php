<?php
declare(strict_types = 1);

namespace App\Entity;

class User
{
    private int $id;
    private string $firstName;
    private string $lastName;
    private string $email;
    private string $ipAddress;
    private float $latitude;
    private float $longitude;

    public function __construct(int $id, string $firstName, string $lastName, string $email, string $ipAddress, float $latitude, float $longitude)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->ipAddress = $ipAddress;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }
}
