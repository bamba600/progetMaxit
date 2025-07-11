<?php

namespace App\Service;

use App\Repository\TransactionRepository;
use App\Repository\CompteRepository;
use App\Entity\Transaction;
use App\Core\App;

class TransactionService
{
    private TransactionRepository $transactionRepository;
    private CompteRepository $compteRepository;

    public function __construct()
    {
        $this->transactionRepository = new TransactionRepository();
        $this->compteRepository = new CompteRepository();
    }

    /**
     * Effectuer un dépôt
     */
    public function deposit(int $compteId, float $montant, string $description = ''): bool
    {
        if ($montant <= 0) {
            return false;
        }

        $compte = $this->compteRepository->findById($compteId);
        if (!$compte) {
            return false;
        }

        // Créer la transaction
        $transaction = new Transaction();
        $transaction->setCompteId($compteId);
        $transaction->setTypeTransaction('depot');
        $transaction->setMontant($montant);
        $transaction->setDescription($description ?: 'Dépôt');
        $transaction->setDateTransaction(new \DateTime());

        // Mettre à jour le solde du compte
        $nouveauSolde = $compte->getSolde() + $montant;
        $compte->setSolde($nouveauSolde);

        // Sauvegarder la transaction et le compte
        $this->transactionRepository->save($transaction);
        $this->compteRepository->save($compte);

        return true;
    }

    /**
     * Effectuer un retrait
     */
    public function withdraw(int $compteId, float $montant, string $description = ''): bool
    {
        if ($montant <= 0) {
            return false;
        }

        $compte = $this->compteRepository->findById($compteId);
        if (!$compte) {
            return false;
        }

        // Vérifier si le solde est suffisant
        if ($compte->getSolde() < $montant) {
            return false;
        }

        // Créer la transaction
        $transaction = new Transaction();
        $transaction->setCompteId($compteId);
        $transaction->setTypeTransaction('retrait');
        $transaction->setMontant($montant);
        $transaction->setDescription($description ?: 'Retrait');
        $transaction->setDateTransaction(new \DateTime());

        // Mettre à jour le solde du compte
        $nouveauSolde = $compte->getSolde() - $montant;
        $compte->setSolde($nouveauSolde);

        // Sauvegarder la transaction et le compte
        $this->transactionRepository->save($transaction);
        $this->compteRepository->save($compte);

        return true;
    }

    /**
     * Effectuer un virement entre comptes
     */
    public function transfer(int $compteSource, int $compteDestination, float $montant, string $description = ''): bool
    {
        if ($montant <= 0) {
            return false;
        }

        $compteS = $this->compteRepository->findById($compteSource);
        $compteD = $this->compteRepository->findById($compteDestination);

        if (!$compteS || !$compteD) {
            return false;
        }

        // Vérifier si le solde est suffisant
        if ($compteS->getSolde() < $montant) {
            return false;
        }

        // Transaction de débit (compte source)
        $transactionDebit = new Transaction();
        $transactionDebit->setCompteId($compteSource);
        $transactionDebit->setTypeTransaction('virement_sortant');
        $transactionDebit->setMontant($montant);
        $transactionDebit->setDescription($description ?: 'Virement sortant');
        $transactionDebit->setDateTransaction(new \DateTime());

        // Transaction de crédit (compte destination)
        $transactionCredit = new Transaction();
        $transactionCredit->setCompteId($compteDestination);
        $transactionCredit->setTypeTransaction('virement_entrant');
        $transactionCredit->setMontant($montant);
        $transactionCredit->setDescription($description ?: 'Virement entrant');
        $transactionCredit->setDateTransaction(new \DateTime());

        // Mettre à jour les soldes
        $compteS->setSolde($compteS->getSolde() - $montant);
        $compteD->setSolde($compteD->getSolde() + $montant);

        // Sauvegarder les transactions et les comptes
        $this->transactionRepository->save($transactionDebit);
        $this->transactionRepository->save($transactionCredit);
        $this->compteRepository->save($compteS);
        $this->compteRepository->save($compteD);

        return true;
    }

    /**
     * Obtenir les transactions d'un compte
     */
    public function getTransactionsByCompte(int $compteId, int $limit = 50): array
    {
        return $this->transactionRepository->findByCompte($compteId, $limit);
    }

    /**
     * Obtenir les transactions d'un utilisateur
     */
    public function getTransactionsByUser(int $utilisateurId, int $limit = 50): array
    {
        return $this->transactionRepository->findByUtilisateur($utilisateurId, $limit);
    }

    /**
     * Calculer le total des transactions par type
     */
    public function getTotalByType(int $compteId, string $type): float
    {
        $transactions = $this->getTransactionsByCompte($compteId);
        $total = 0.0;

        foreach ($transactions as $transaction) {
            if ($transaction->getTypeTransaction() === $type) {
                $total += $transaction->getMontant();
            }
        }

        return $total;
    }

    /**
     * Obtenir les transactions récentes
     */
    public function getRecentTransactions(int $utilisateurId, int $limit = 10): array
    {
        return $this->transactionRepository->findRecentByUtilisateur($utilisateurId, $limit);
    }
}
