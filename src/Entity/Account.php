<?php

namespace App\Entity;

use App\Repository\AccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use \App\Entity\Enum\CurrencyEnum;

#[ORM\Entity(repositoryClass: AccountRepository::class)]
class Account
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $number = null;

    #[ORM\Column(enumType: CurrencyEnum::class)]
    private ?CurrencyEnum $currency = null;

    #[ORM\ManyToOne(inversedBy: 'accounts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2)]
    private ?string $balance = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'fromAccount')]
    private Collection $outgoingTransactions;

    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'toAccount')]
    private Collection $incomingTransactions;

    public function __construct()
    {
        $this->outgoingTransactions = new ArrayCollection();
        $this->incomingTransactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getCurrency(): ?CurrencyEnum
    {
        return $this->currency;
    }

    public function setCurrency(CurrencyEnum $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getBalance(): ?string
    {
        return $this->balance;
    }

    public function setBalance(string $balance): static
    {
        $this->balance = $balance;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getIncomingTransactions(): Collection
    {
        return $this->incomingTransactions;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getOutgoingTransactions(): Collection
    {
        return $this->outgoingTransactions;
    }

    /**
     * @return Transaction[]
     */
    public function getTransactions(): array
    {
        $transactions = array_merge(
            $this->outgoingTransactions->toArray(),
            $this->incomingTransactions->toArray()
        );

        usort($transactions, function (Transaction $a, Transaction $b) {
            return $b->getCreatedAt() <=> $a->getCreatedAt();
        });

        return $transactions;
    }
}
