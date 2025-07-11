<?php

namespace App\Repository;

use App\Core\Abstract\AbstractRepository;
use App\Entity\Compte;
use App\Entity\TypeCompte;
use PDO;

class CompteRepository extends AbstractRepository
{
    protected string $table = 'compte';

    public function findByUtilisateurId(int $utilisateurId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE utilisateur_id = :utilisateur_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['utilisateur_id' => $utilisateurId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $comptes = [];
        foreach ($results as $data) {
            $comptes[] = $this->toObject($data);
        }
        
        return $comptes;
    }

    public function findComptePrincipalByUtilisateurId(int $utilisateurId): ?Compte
    {
        $sql = "SELECT * FROM {$this->table} WHERE utilisateur_id = :utilisateur_id AND type = 'compte_principal'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['utilisateur_id' => $utilisateurId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) {
            return null;
        }

        return $this->toObject($data);
    }

    public function findByNumero(string $numero): ?Compte
    {
        $sql = "SELECT * FROM {$this->table} WHERE numero = :numero";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['numero' => $numero]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) {
            return null;
        }

        return $this->toObject($data);
    }

    public function create(Compte $compte): int
    {
        $data = [
            'solde' => $compte->getSolde(),
            'numero' => $compte->getNumero(),
            'utilisateur_id' => $compte->getUtilisateurId(),
            'type' => $compte->getType()
        ];

        return $this->save($data);
    }

    public function updateSolde(int $id, float $nouveauSolde): bool
    {
        $sql = "UPDATE {$this->table} SET solde = :solde WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['solde' => $nouveauSolde, 'id' => $id]);
    }

    public function existsByNumero(string $numero): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE numero = :numero";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['numero' => $numero]);
        return $stmt->fetchColumn() > 0;
    }

    protected function toObject(array $data): object
    {
        $compte = new Compte();
        $compte->setId($data['id']);
        $compte->setSolde($data['solde']);
        $compte->setNumero($data['numero']);
        $compte->setUtilisateurId($data['utilisateur_id']);
        $compte->setType($data['type']);

        return $compte;
    }
}
