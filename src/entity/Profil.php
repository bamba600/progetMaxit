<?php

namespace App\Entity;

use App\Core\Abstract\AbstractEntity;

class Profil extends AbstractEntity
{
    private ?int $id = null;
    private bool $client = false;
    private bool $serviceCommercial = false;
    private int $utilisateurId;
    private ?\DateTime $createdAt = null;

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

    public function isClient(): bool
    {
        return $this->client;
    }

    public function setClient(bool $client): self
    {
        $this->client = $client;
        return $this;
    }

    public function isServiceCommercial(): bool
    {
        return $this->serviceCommercial;
    }

    public function setServiceCommercial(bool $serviceCommercial): self
    {
        $this->serviceCommercial = $serviceCommercial;
        return $this;
    }

    public function getUtilisateurId(): int
    {
        return $this->utilisateurId;
    }

    public function setUtilisateurId(int $utilisateurId): self
    {
        $this->utilisateurId = $utilisateurId;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Obtenir le type de profil sous forme de chaîne
     */
    public function getType(): string
    {
        if ($this->serviceCommercial) {
            return 'service_commercial';
        }
        
        if ($this->client) {
            return 'client';
        }
        
        return 'aucun';
    }

    public static function toObject(array $data): object
    {
        $profil = new self();
        
        if (isset($data['id'])) {
            $profil->setId($data['id']);
        }
        
        $profil->setClient((bool)($data['client'] ?? false))
            ->setServiceCommercial((bool)($data['service_commercial'] ?? false))
            ->setUtilisateurId($data['utilisateur_id'] ?? 0);
            
        if (isset($data['created_at'])) {
            $profil->setCreatedAt(new \DateTime($data['created_at']));
        }

        return $profil;
    }

    public static function toArray(object $object): array
    {
        if (!$object instanceof self) {
            throw new \InvalidArgumentException("L'objet doit être une instance de Profil");
        }

        return [
            'id' => $object->getId(),
            'client' => $object->isClient(),
            'service_commercial' => $object->isServiceCommercial(),
            'utilisateur_id' => $object->getUtilisateurId(),
            'created_at' => $object->getCreatedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
