<?php

namespace App\Controller;

use App\Core\Abstract\AbstractController;
use App\Core\Validator;

class ControllerConnection extends AbstractController
{
    public function __construct()
    {
        parent::__construct();
        $this->layout = null; // Pas de layout pour les pages de connexion
    }

    public function index() 
    {
        // Si l'utilisateur est déjà connecté, rediriger vers le tableau de bord
        if ($this->authService->isAuthenticated()) {
            $this->redirect('/tableau-de-bord');
        }

        // Récupérer le message de succès s'il existe
        $successMessage = null;
        if ($this->session->isset('creation_success')) {
            $successMessage = $this->session->get('creation_success');
            $this->session->unset('creation_success');
        }

        $this->renderHtml('connexion.php', ['success' => $successMessage]);
    }

    public function login() 
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/connexion');
        }

        $login = trim($_POST['login'] ?? '');
        $motDePasse = trim($_POST['mot_de_passe'] ?? '');

        $errors = [];

        // Validation avec la classe Validator
        $rules = [
            'login' => ['required'],
            'mot_de_passe' => ['required']
        ];

        $data = [
            'login' => $login,
            'mot_de_passe' => $motDePasse
        ];

        Validator::validate($data, $rules);
        $errors = Validator::getErrors();

        if (!empty($errors)) {
            $this->renderHtml('connexion.php', ['errors' => $errors, 'login' => $login]);
            return;
        }

        // Authentifier l'utilisateur avec le service
        if ($this->authService->authenticate($login, $motDePasse)) {
            $this->redirect('/tableau-de-bord');
        } else {
            $this->renderHtml('connexion.php', [
                'errors' => ['general' => 'Login ou mot de passe incorrect'],
                'login' => $login
            ]);
        }
    }

    public function logout() 
    {
        $this->authService->logout();
        $this->redirect('/connexion');
    }

    public function create() 
    {
        $this->redirect('/creer-compte');
    }

    public function store() 
    {
        $this->redirect('/creer-compte');
    }

    public function show($id = null) 
    {
        $this->redirect('/connexion');
    }

    public function edit($id) 
    {
        $this->redirect('/connexion');
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
