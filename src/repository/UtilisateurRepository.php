<?php

namespace App\Repository;

use App\Core\Abstract\AbstractRepository;
use App\Entity\Utilisateur;
use App\Entity\Profil;
use PDO;

class UtilisateurRepository extends AbstractRepository
{
    protected string $table = 'utilisateur';

    public function findByLogin(string $login): ?Utilisateur
    {
        $sql = "SELECT * FROM {$this->table} WHERE login = :login";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['login' => $login]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) {
            return null;
        }

        return $this->toObject($data);
    }

    public function findByIdEntity(int $id): ?Utilisateur
    {
        $data = parent::findById($id);
        if (!$data) {
            return null;
        }
        return $this->toObject($data);
    }

    public function create(Utilisateur $utilisateur): int
    {
        $data = [
            'nom' => $utilisateur->getNom(),
            'prenom' => $utilisateur->getPrenom(),
            'login' => $utilisateur->getLogin(),
            'mot_de_passe' => $utilisateur->getMotDePasse(),
            'numeroTelephone' => $utilisateur->getNumeroTelephone(),
            'numeroCNI' => $utilisateur->getNumeroCNI(),
            'adresse' => $utilisateur->getAdresse(),
            'photorecto' => $utilisateur->getPhotoRecto(),
            'photoverso' => $utilisateur->getPhotoVerso()
        ];

        return $this->save($data);
    }

    public function updateUtilisateur(int $id, Utilisateur $utilisateur): bool
    {
        $data = [
            'nom' => $utilisateur->getNom(),
            'prenom' => $utilisateur->getPrenom(),
            'login' => $utilisateur->getLogin(),
            'numeroTelephone' => $utilisateur->getNumeroTelephone(),
            'numeroCNI' => $utilisateur->getNumeroCNI(),
            'adresse' => $utilisateur->getAdresse(),
            'photorecto' => $utilisateur->getPhotoRecto(),
            'photoverso' => $utilisateur->getPhotoVerso()
        ];

        return $this->update($id, $data);
    }

    public function existsByLogin(string $login): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE login = :login";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['login' => $login]);
        return $stmt->fetchColumn() > 0;
    }

    public function existsByCNI(string $numeroCNI): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE numeroCNI = :numeroCNI";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['numeroCNI' => $numeroCNI]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Rechercher un utilisateur par son numéro CNI
     */
    public function findByCNI(string $numeroCNI): ?Utilisateur
    {
        $query = "SELECT * FROM {$this->table} WHERE numeroCNI = :numeroCNI";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['numeroCNI' => $numeroCNI]);
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            return $this->toObject($data);
        }
        
        return null;
    }

    /**
     * Rechercher un utilisateur par son numéro de téléphone
     */
    public function findByPhone(string $numeroTelephone): ?Utilisateur
    {
        $query = "SELECT * FROM {$this->table} WHERE numeroTelephone = :numeroTelephone";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['numeroTelephone' => $numeroTelephone]);
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            return $this->toObject($data);
        }
        
        return null;
    }

    protected function toObject(array $data): object
    {
        $utilisateur = new Utilisateur();
        $utilisateur->setId($data['id']);
        $utilisateur->setNom($data['nom']);
        $utilisateur->setPrenom($data['prenom'] ?? '');
        $utilisateur->setLogin($data['login']);
        $utilisateur->setMotDePasseHash($data['mot_de_passe']); // Utiliser setMotDePasseHash pour ne pas re-hasher
        $utilisateur->setNumeroTelephone($data['numeroTelephone'] ?? '');
        $utilisateur->setNumeroCNI($data['numeroCNI'] ?? '');
        $utilisateur->setAdresse($data['adresse'] ?? '');
        $utilisateur->setPhotoRecto($data['photorecto'] ?? '');
        $utilisateur->setPhotoVerso($data['photoverso'] ?? '');

        return $utilisateur;
    }
}
