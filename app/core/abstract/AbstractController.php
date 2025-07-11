<?php

namespace App\Core\Abstract;

use App\Core\App;
use App\Service\AuthService;

abstract class AbstractController 
{
    protected $layout = 'base.layout.php';
    protected $session; // Session centralisée
    protected $db;      // PDO centralisé
    protected AuthService $authService; // Service d'authentification

    public function __construct()
    {
        $this->session = App::getDependency('session');
        $this->db = App::getDependency('db'); // Ajout : accès PDO via App
        $this->authService = new AuthService(); // Initialiser le service d'authentification
    }

    abstract public function index();

    abstract public function store();

    abstract public function create();

    abstract public function destroy($id);

    abstract public function show($id = null);

    abstract public function edit($id);

    protected function renderHtml(string $view, array $data = [])
    {
        extract($data);
        ob_start();
        require_once __DIR__ . '/../../../templates/' . $view;
        $contentForLayout = ob_get_clean();

        // Si pas de layout, afficher directement le contenu
        if ($this->layout === null) {
            echo $contentForLayout;
        } else {
            require_once __DIR__ . '/../../../templates/layout/' . $this->layout;
        }
    }

    protected function redirect(string $url)
    {
        header("Location: $url");
        exit;
    }

    protected function json(array $data, int $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function getCurrentUser(): ?array
    {
        return $this->authService->user();
    }

    protected function getCurrentUserId(): ?int
    {
        return $this->authService->userId();
    }
}
?>

