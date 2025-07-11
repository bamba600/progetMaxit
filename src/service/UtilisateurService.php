<?php

namespace App\Service;

use App\Repository\UtilisateurRepository;
use App\Repository\ProfilRepository;
use App\Entity\Utilisateur;
use App\Entity\Profil;
use App\Core\Validator;
use App\Core\Middlewares\CryptPassword;
use App\Core\Middlewares\DecryptPassword;

class UtilisateurService
{
    private UtilisateurRepository $utilisateurRepository;
    private ProfilRepository $profilRepository;

    public function __construct()
    {
        $this->utilisateurRepository = new UtilisateurRepository();
        $this->profilRepository = new ProfilRepository();
    }

    /**
     * Créer un nouvel utilisateur
     */
    public function createUtilisateur(array $data): ?Utilisateur
    {
        // Validation des données
        $errors = $this->validateUserData($data);
        if (!empty($errors)) {
            return null;
        }

        // Vérifier si l'utilisateur existe déjà
        if ($this->utilisateurRepository->findByLogin($data['login'])) {
            return null;
        }

        $utilisateur = new Utilisateur();
        $utilisateur->setNom($data['nom']);
        $utilisateur->setPrenom($data['prenom']);
        $utilisateur->setLogin($data['login']);
        
        // Le mot de passe sera automatiquement hashé par le middleware CryptPassword
        // ou nous utilisons la méthode statique si besoin
        if (isset($data['mot_de_passe'])) {
            if (DecryptPassword::isValidHash($data['mot_de_passe'])) {
                // Déjà hashé (par le middleware ou autre)
                $utilisateur->setMotDePasseHash($data['mot_de_passe']);
            } else {
                // Pas encore hashé, on le fait
                $hashedPassword = CryptPassword::hashPassword($data['mot_de_passe']);
                $utilisateur->setMotDePasseHash($hashedPassword);
            }
        }
        
        $utilisateur->setNumeroTelephone($data['telephone'] ?? '');
        $utilisateur->setNumeroCNI($data['numero_cni'] ?? '');
        $utilisateur->setAdresse($data['adresse'] ?? '');
        $utilisateur->setPhotoRecto($data['photo_recto'] ?? '');
        $utilisateur->setPhotoVerso($data['photo_verso'] ?? '');

        $utilisateurId = $this->utilisateurRepository->create($utilisateur);
        
        // Créer le profil pour cet utilisateur
        if ($utilisateurId) {
            $typeProfil = $data['profil'] ?? 'client';
            $this->profilRepository->createForUtilisateur($utilisateurId, $typeProfil);
            
            // Récupérer l'utilisateur créé
            $utilisateur->setId($utilisateurId);
            return $utilisateur;
        }

        return null;
    }

    /**
     * Valider les données utilisateur
     */
    private function validateUserData(array $data): array
    {
        $rules = [
            'nom' => ['required', 'name'],
            'prenom' => ['required', 'name'],
            'login' => [
                'required',
                'min_length' => [
                    'params' => [3],
                    'message' => 'Le login doit contenir au moins 3 caractères'
                ],
                'login'
            ],
            'mot_de_passe' => [
                'required',
                'min_length' => [
                    'params' => [6],
                    'message' => 'Le mot de passe doit contenir au moins 6 caractères'
                ]
            ]
        ];

        // Ajouter validation email si fourni
        if (!empty($data['email'])) {
            $rules['email'] = ['email'];
        }

        // Ajouter validation téléphone si fourni
        if (!empty($data['telephone'])) {
            $rules['telephone'] = ['phone'];
        }

        // Valider avec la classe Validator
        Validator::validate($data, $rules);
        return Validator::getErrors();
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function updateUtilisateur(int $id, array $data): ?Utilisateur
    {
        $utilisateur = $this->utilisateurRepository->findUserById($id);
        if (!$utilisateur) {
            return null;
        }

        // Mettre à jour les champs modifiables
        if (isset($data['nom'])) {
            $utilisateur->setNom($data['nom']);
        }
        if (isset($data['prenom'])) {
            $utilisateur->setPrenom($data['prenom']);
        }
        if (isset($data['telephone'])) {
            $utilisateur->setTelephone($data['telephone']);
        }
        if (isset($data['email'])) {
            $utilisateur->setEmail($data['email']);
        }
        if (isset($data['profil'])) {
            $utilisateur->setProfil($data['profil']);
        }

        return $this->utilisateurRepository->save($utilisateur);
    }

    /**
     * Changer le mot de passe
     */
    public function changePassword(int $id, string $oldPassword, string $newPassword): bool
    {
        $utilisateur = $this->utilisateurRepository->findUserById($id);
        if (!$utilisateur) {
            return false;
        }

        // Vérifier l'ancien mot de passe avec DecryptPassword
        if (!DecryptPassword::verifyPassword($oldPassword, $utilisateur->getMotDePasse())) {
            return false;
        }

        // Mettre à jour le mot de passe avec CryptPassword
        $hashedPassword = CryptPassword::hashPassword($newPassword);
        $utilisateur->setMotDePasseHash($hashedPassword);
        $this->utilisateurRepository->save($utilisateur);

        return true;
    }

    /**
     * Désactiver un utilisateur
     */
    public function deactivateUtilisateur(int $id): bool
    {
        $utilisateur = $this->utilisateurRepository->findUserById($id);
        if (!$utilisateur) {
            return false;
        }

        $utilisateur->setActif(false);
        $this->utilisateurRepository->save($utilisateur);

        return true;
    }

    /**
     * Obtenir un utilisateur par ID
     */
    public function getUtilisateurById(int $id): ?Utilisateur
    {
        return $this->utilisateurRepository->findUserById($id);
    }

    /**
     * Obtenir un utilisateur par login
     */
    public function getUtilisateurByLogin(string $login): ?Utilisateur
    {
        return $this->utilisateurRepository->findByLogin($login);
    }

    /**
     * Obtenir tous les utilisateurs
     */
    public function getAllUtilisateurs(): array
    {
        return $this->utilisateurRepository->findAll();
    }

    /**
     * Rechercher des utilisateurs par nom/prénom
     */
    public function searchUtilisateurs(string $query): array
    {
        return $this->utilisateurRepository->search($query);
    }

    /**
     * Obtenir le profil d'un utilisateur
     */
    public function getProfilByUtilisateur(int $utilisateurId): ?Profil
    {
        return $this->profilRepository->findByUtilisateurId($utilisateurId);
    }

    /**
     * Vérifier si un utilisateur a un profil spécifique
     */
    public function hasProfile(int $utilisateurId, string $profileType): bool
    {
        $profil = $this->getProfilByUtilisateur($utilisateurId);
        
        if (!$profil) {
            return false;
        }
        
        return $profil->getType() === $profileType;
    }

    /**
     * Vérifier si un utilisateur est un client
     */
    public function isClient(int $utilisateurId): bool
    {
        return $this->hasProfile($utilisateurId, 'client');
    }

    /**
     * Vérifier si un utilisateur est du service commercial
     */
    public function isServiceCommercial(int $utilisateurId): bool
    {
        return $this->hasProfile($utilisateurId, 'service_commercial');
    }

    /**
     * Mettre à jour le profil d'un utilisateur
     */
    public function updateProfil(int $utilisateurId, string $typeProfil): bool
    {
        return $this->profilRepository->updateProfil($utilisateurId, $typeProfil);
    }

    /**
     * Vérifier si un utilisateur existe par login
     */
    public function existsByLogin(string $login): bool
    {
        return $this->utilisateurRepository->findByLogin($login) !== null;
    }

    /**
     * Vérifier si un utilisateur existe par numéro CNI
     */
    public function existsByCNI(string $numeroCNI): bool
    {
        return $this->utilisateurRepository->findByCNI($numeroCNI) !== null;
    }

    /**
     * Vérifier si un numéro de téléphone existe déjà
     */
    public function phoneExists(string $phone): bool
    {
        return $this->utilisateurRepository->findByPhone($phone) !== null;
    }

    /**
     * Vérifier si une CNI existe déjà
     */
    public function cniExists(string $cni): bool
    {
        return $this->existsByCNI($cni);
    }

    /**
     * Vérifier si un login existe déjà
     */
    public function loginExists(string $login): bool
    {
        return $this->existsByLogin($login);
    }
}
