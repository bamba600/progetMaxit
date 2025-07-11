<?php

namespace App\Service;

use App\Repository\UtilisateurRepository;
use App\Repository\ProfilRepository;
use App\Core\Session;
use App\Core\App;
use App\Core\Middlewares\DecryptPassword;

class AuthService
{
    private UtilisateurRepository $utilisateurRepository;
    private ProfilRepository $profilRepository;
    private Session $session;

    public function __construct()
    {
        $this->utilisateurRepository = new UtilisateurRepository();
        $this->profilRepository = new ProfilRepository();
        $this->session = App::getDependency('session');
    }

    /**
     * Authentifier un utilisateur
     */
    public function authenticate(string $login, string $password): bool
    {
        $utilisateur = $this->utilisateurRepository->findByLogin($login);
        
        if (!$utilisateur) {
            return false;
        }

        // Vérifier le mot de passe avec DecryptPassword
        if (!DecryptPassword::verifyPassword($password, $utilisateur->getMotDePasse())) {
            return false;
        }

        // Créer la session
        $this->session->set('user_id', $utilisateur->getId());
        $this->session->set('user_login', $utilisateur->getLogin());
        
        // Récupérer et stocker le profil
        $profil = $this->profilRepository->findByUtilisateurId($utilisateur->getId());
        if ($profil) {
            $this->session->set('user_profil', $profil->getType());
        } else {
            $this->session->set('user_profil', 'client'); // Par défaut
        }
        
        return true;
    }

    /**
     * Déconnecter l'utilisateur
     */
    public function logout(): void
    {
        $this->session->destroy();
    }

    /**
     * Vérifier si l'utilisateur est connecté
     */
    public function isAuthenticated(): bool
    {
        return $this->session->isset('user_id');
    }

    /**
     * Obtenir l'utilisateur connecté
     */
    public function getAuthenticatedUser(): ?object
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        $userId = $this->session->get('user_id');
        return $this->utilisateurRepository->findUserById($userId);
    }

    /**
     * Vérifier si l'utilisateur a un profil spécifique
     */
    public function hasProfile(string $profile): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        return $this->session->get('user_profil') === $profile;
    }

    /**
     * Vérifier si l'utilisateur est un vendeur
     */
    public function isVendeur(): bool
    {
        return $this->hasProfile('vendeur');
    }

    /**
     * Vérifier si l'utilisateur est un admin
     */
    public function isAdmin(): bool
    {
        return $this->hasProfile('admin');
    }

    /**
     * Vérifier si l'utilisateur est connecté
     */
    public function check(): bool
    {
        return $this->session && $this->session->isset('user_id');
    }

    /**
     * Obtenir les données de l'utilisateur connecté
     */
    public function user(): ?array
    {
        if (!$this->session || !$this->session->isset('user_id')) {
            return null;
        }

        return [
            'id' => $this->session->get('user_id'),
            'nom' => $this->session->get('user_nom'),
            'profil' => $this->session->get('user_profil'),
            'login' => $this->session->get('user_login')
        ];
    }

    /**
     * Obtenir l'ID de l'utilisateur connecté
     */
    public function userId(): ?int
    {
        return $this->session ? $this->session->get('user_id') : null;
    }

    /**
     * Vérifier si l'utilisateur est un client
     */
    public function isClient(): bool
    {
        return $this->session && $this->session->get('user_profil') === 'client';
    }

    /**
     * Vérifier si l'utilisateur est du service commercial
     */
    public function isServiceCommercial(): bool
    {
        return $this->session && $this->session->get('user_profil') === 'service_commercial';
    }

    /**
     * Vérifier l'expiration de la session
     */
    public function checkSessionExpiry(): void
    {
        $lastActivity = $this->session->get('last_activity');
        $sessionTimeout = 3600; // 1 heure en secondes
        
        if ($lastActivity && (time() - $lastActivity) > $sessionTimeout) {
            $this->session->destroy();
            header('Location: /connexion?expired=1');
            exit;
        }
        
        // Mettre à jour la dernière activité
        $this->session->set('last_activity', time());
    }
}
