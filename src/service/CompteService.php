<?php

namespace App\Service;

use App\Repository\CompteRepository;
use App\Entity\Compte;
use App\Core\App;

class CompteService
{
    private CompteRepository $compteRepository;

    public function __construct()
    {
        $this->compteRepository = new CompteRepository();
    }

    /**
     * Créer un nouveau compte
     */
    public function createCompte(int $utilisateurId, string $typeCompte, float $soldeInitial = 0.0): Compte
    {
        $compte = new Compte();
        $compte->setUtilisateurId($utilisateurId);
        
        // Adapter le type selon la base de données
        $typeDB = $typeCompte === 'principal' ? 'compte_principal' : 'compte_secondaire';
        $compte->setType($typeDB);
        $compte->setSolde($soldeInitial);
        
        // Générer un numéro de compte unique
        $numero = $this->generateUniqueNumero();
        $compte->setNumero($numero);

        $compteId = $this->compteRepository->create($compte);
        $compte->setId($compteId);
        
        return $compte;
    }

    /**
     * Générer un numéro de compte unique
     */
    private function generateUniqueNumero(): string
    {
        do {
            $numero = 'CPT' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while ($this->compteRepository->existsByNumero($numero));
        
        return $numero;
    }

    /**
     * Obtenir tous les comptes d'un utilisateur
     */
    public function getComptesByUser(int $utilisateurId): array
    {
        return $this->compteRepository->findByUtilisateur($utilisateurId);
    }

    /**
     * Obtenir un compte par ID
     */
    public function getCompteById(int $id): ?Compte
    {
        return $this->compteRepository->findById($id);
    }

    /**
     * Vérifier si un compte appartient à un utilisateur
     */
    public function isCompteOwnedByUser(int $compteId, int $utilisateurId): bool
    {
        $compte = $this->getCompteById($compteId);
        return $compte && $compte->getUtilisateurId() === $utilisateurId;
    }

    /**
     * Mettre à jour le solde d'un compte
     */
    public function updateSolde(int $compteId, float $nouveauSolde): bool
    {
        $compte = $this->getCompteById($compteId);
        if (!$compte) {
            return false;
        }

        $compte->setSolde($nouveauSolde);
        $this->compteRepository->save($compte);
        return true;
    }

    /**
     * Désactiver un compte
     */
    public function deactivateCompte(int $compteId): bool
    {
        $compte = $this->getCompteById($compteId);
        if (!$compte) {
            return false;
        }

        $compte->setActif(false);
        $this->compteRepository->save($compte);
        return true;
    }

    /**
     * Calculer le solde total d'un utilisateur
     */
    public function getTotalSoldeByUser(int $utilisateurId): float
    {
        $comptes = $this->getComptesByUser($utilisateurId);
        $total = 0.0;
        
        foreach ($comptes as $compte) {
            if ($compte->isActif()) {
                $total += $compte->getSolde();
            }
        }
        
        return $total;
    }

    /**
     * Vérifier si un compte a suffisamment de fonds
     */
    public function hasSufficientFunds(int $compteId, float $montant): bool
    {
        $compte = $this->getCompteById($compteId);
        return $compte && $compte->getSolde() >= $montant;
    }

    /**
     * Vérifier si un compte existe par numéro
     */
    public function existsByNumero(string $numero): bool
    {
        return $this->compteRepository->findByNumero($numero) !== null;
    }
}
