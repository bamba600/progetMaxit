<?php

namespace App\Entity;

use App\Core\Abstract\AbstractEntity;

class Compte extends AbstractEntity
{
    private ?int $id = null;
    private float $solde;
    private string $numero;
    private int $utilisateurId;
    private string $type;
    private array $transactions = [];
    private $utilisateur;

   
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
    public function getSolde(): float
    {
        return $this->solde;
    }
    
    public function setSolde(float $solde): self
    {
        $this->solde = $solde;
        return $this;
    }
    public function getNumero(): string
    {
        return $this->numero;
    }
    
    public function setNumero(string $numero): self
    {
        $this->numero = $numero;
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
    public function getType(): string
    {
        return $this->type;
    }
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }
   
    public function setUtilisateur(Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }
    public function getTransactions(): array
    {
        return $this->transactions;
    }
    public function setTransactions(array $transactions): self

    {
        $this->transactions = $transactions;
        return $this;
    }
    public function addTransaction(Transaction $transaction): self
    {
        $this->transactions[] = $transaction;
        return $this;
    }

    public static function toObject(array $data): object
    {
        $compte = new self();
        $compte->setSolde($data['solde'] ?? 0.0)
               ->setNumero($data['numero'] ?? '')
               ->setUtilisateurId($data['utilisateurId'] ?? 0)
               ->setType($data['type'] ?? TypeCompte::PRINCIPAL);
        
        if (isset($data['utilisateur'])) {
            $compte->setUtilisateur(Utilisateur::toObject($data['utilisateur']));
        }

        if (isset($data['transactions'])) {
            foreach ($data['transactions'] as $transactionData) {
                $compte->addTransaction(Transaction::toObject($transactionData));
            }
        }

        return $compte;
    }
    public static function toArray(object $object): array
    {
        if (!$object instanceof self) {
            throw new \InvalidArgumentException('L\'objet doit être une instance de Compte');
        }

        $data = [
            'solde' => $object->getSolde(),
            'numero' => $object->getNumero(),
            'utilisateurId' => $object->getUtilisateurId(),
            'type' => $object->getType(),
        ];

        if ($object->getUtilisateur()) {
            $data['utilisateur'] = Utilisateur::toArray($object->getUtilisateur());
        }

        if ($object->getTransactions()) {
            $data['transactions'] = array_map(fn($transaction) => Transaction::toArray($transaction), $object->getTransactions());
        }

        return $data;
    }


    



    
}
