<?php

namespace App\Entity;

use App\Enum\Status;
use App\Repository\ScheduleSlotRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DateTimeInterface;

#[ORM\Entity(repositoryClass: ScheduleSlotRepository::class)]
class ScheduleSlot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private DateTimeInterface $start;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private DateTimeInterface $end;

    #[ORM\Column]
    private float $price;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $doctor;

    #[ORM\ManyToOne]
    private ?User $patient = null;

    #[ORM\Column(enumType: Status::class, options: ['default' => Status::NotPaid])]
    private Status $status;

    #[ORM\Column(length: 1024, nullable: true)]
    private ?string $paymentLink = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $recommendation = null;

    public function __construct(
        DateTimeInterface $start,
        DateTimeInterface $end,
        float $price,
        User $doctor,
    ) {
        $this->start = $start;
        $this->end = $end;
        $this->price = $price;
        $this->doctor = $doctor;
        $this->status = Status::NotPaid;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStart(): DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(DateTimeInterface $start): static
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(DateTimeInterface $end): static
    {
        $this->end = $end;

        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getDoctor(): User
    {
        return $this->doctor;
    }

    public function setDoctor(User $doctor): static
    {
        $this->doctor = $doctor;

        return $this;
    }

    public function getPatient(): ?User
    {
        return $this->patient;
    }

    public function setPatient(?User $patient): static
    {
        $this->patient = $patient;

        return $this;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getPaymentLink(): ?string
    {
        return $this->paymentLink;
    }

    public function setPaymentLink(?string $paymentLink): static
    {
        $this->paymentLink = $paymentLink;

        return $this;
    }

    public function getRecommendation(): ?string
    {
        return $this->recommendation;
    }

    public function setRecommendation(?string $recommendation): static
    {
        $this->recommendation = $recommendation;

        return $this;
    }
}
