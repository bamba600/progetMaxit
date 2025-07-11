<?php

namespace App\Controller;

use App\Core\Abstract\AbstractController;
use App\Repository\UtilisateurRepository;
use App\Repository\CompteRepository;
use App\Repository\TransactionRepository;

class ControllerTableauDeBord extends AbstractController
{
    private UtilisateurRepository $utilisateurRepository;
    private CompteRepository $compteRepository;
    private TransactionRepository $transactionRepository;

    public function __construct()
    {
        parent::__construct();
        $this->utilisateurRepository = new UtilisateurRepository();
        $this->compteRepository = new CompteRepository();
        $this->transactionRepository = new TransactionRepository();
    }

    public function index() 
    {
        // Vérifier si l'utilisateur est connecté
        if (!$this->session->isset('user_id')) {
            $this->redirect('/connexion');
        }

        $userId = $this->session->get('user_id');
        
        // Récupérer les données de l'utilisateur
        $utilisateur = $this->utilisateurRepository->findByIdEntity($userId);
        if (!$utilisateur) {
            $this->session->destroy();
            $this->redirect('/connexion');
        }

        // Récupérer le compte principal
        $comptePrincipal = $this->compteRepository->findComptePrincipalByUtilisateurId($userId);
        
        // Récupérer tous les comptes
        $comptes = $this->compteRepository->findByUtilisateurId($userId);

        // Récupérer les 10 dernières transactions
        $dernieresTransactions = [];
        if ($comptePrincipal) {
            $dernieresTransactions = $this->transactionRepository->findDernieresTransactionsByCompteId($comptePrincipal->getId(), 10);
        }

        // Calculer les statistiques
        $statistiques = $this->calculerStatistiques($comptes);

        $data = [
            'utilisateur' => $utilisateur,
            'comptePrincipal' => $comptePrincipal,
            'comptes' => $comptes,
            'dernieresTransactions' => $dernieresTransactions,
            'statistiques' => $statistiques
        ];

        $this->renderHtml('tablauDeBord.php', $data);
    }

    private function calculerStatistiques(array $comptes): array
    {
        $totalSolde = 0;
        $nombreComptes = count($comptes);
        
        foreach ($comptes as $compte) {
            $totalSolde += $compte->getSolde();
        }

        return [
            'totalSolde' => $totalSolde,
            'nombreComptes' => $nombreComptes,
            'moyenneSolde' => $nombreComptes > 0 ? $totalSolde / $nombreComptes : 0
        ];
    }

    public function create() 
    {
        $this->redirect('/tableau-de-bord');
    }

    public function store() 
    {
        $this->redirect('/tableau-de-bord');
    }

    public function show($id = null) 
    {
        $this->redirect('/tableau-de-bord');
    }

    public function edit($id) 
    {
        $this->redirect('/tableau-de-bord');
    }

    public function update($id) 
    {
        // Non implémenté
    }

    public function destroy($id) 
    {
        // Non implémenté
    }
}
