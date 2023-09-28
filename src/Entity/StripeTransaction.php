<?php

namespace App\Entity;

use App\Repository\StripeTransactionRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StripeTransactionRepository::class)]
class StripeTransaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private string $objectId;

    #[ORM\Column]
    private int $amount;

    #[ORM\Column(length: 255)]
    private string $currency;

    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $stripeCreatedAt;

    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $serverCreatedAt;

    public function __construct(
        string $objectId,
        int $amount,
        string $currency,
        DateTimeImmutable $stripeCreatedAt,
    ) {
        $this->objectId = $objectId;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->stripeCreatedAt = $stripeCreatedAt;
        $this->serverCreatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getObjectId(): string
    {
        return $this->objectId;
    }

    public function setObjectId(string $objectId): static
    {
        $this->objectId = $objectId;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getStripeCreatedAt(): DateTimeImmutable
    {
        return $this->stripeCreatedAt;
    }

    public function setStripeCreatedAt(DateTimeImmutable $stripeCreatedAt): static
    {
        $this->stripeCreatedAt = $stripeCreatedAt;

        return $this;
    }

    public function getServerCreatedAt(): DateTimeImmutable
    {
        return $this->serverCreatedAt;
    }

    public function setServerCreatedAt(DateTimeImmutable $serverCreatedAt): static
    {
        $this->serverCreatedAt = $serverCreatedAt;

        return $this;
    }
}
