<?php

namespace App\Entity;

use App\Core\Abstract\AbstractEntity;
use DateTime;

class Transaction extends AbstractEntity
{
    private ?int $id = null;
    private DateTime $date;
    private float $montant;
    private int $compteId;
    private string $type;
    private string $description = '';
    private string $reference = '';
    private $compte;

    public function __construct()
    {
        // Constructeur vide pour permettre l'hydratation
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }
    
    public function setDate(DateTime $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getMontant(): float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;
        return $this;
    }

    public function getCompteId(): int
    {
        return $this->compteId;
    }
    
    public function setCompteId(int $compteId): self
    {
        $this->compteId = $compteId;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }
   
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;
        return $this;
    }

    public function getCompte()
    {
        return $this->compte;
    }

    public function setCompte($compte): self
    {
        $this->compte = $compte;
        return $this;
    }

    public static function toObject(array $data): object
    {
        $transaction = new self();
        
        if (isset($data['id'])) {
            $transaction->setId($data['id']);
        }
        
        $transaction->setDate(new DateTime($data['date'] ?? 'now'))
            ->setMontant($data['montant'] ?? 0.0)
            ->setCompteId($data['compte_id'] ?? 0)
            ->setType($data['type'] ?? '')
            ->setDescription($data['description'] ?? '')
            ->setReference($data['reference'] ?? '');

        return $transaction;
    }

    public static function toArray(object $object): array
    {
        if (!$object instanceof self) {
            throw new \InvalidArgumentException("L'objet doit être une instance de Transaction");
        }

        return [
            'id' => $object->getId(),
            'date' => $object->getDate()->format('Y-m-d H:i:s'),
            'montant' => $object->getMontant(),
            'compte_id' => $object->getCompteId(),
            'type' => $object->getType(),
            'description' => $object->getDescription(),
            'reference' => $object->getReference(),
        ];
    }
}
