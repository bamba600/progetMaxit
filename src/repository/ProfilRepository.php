<?php

namespace App\Repository;

use App\Core\Abstract\AbstractRepository;
use App\Entity\Profil;
use PDO;

class ProfilRepository extends AbstractRepository
{
    protected string $table = 'profil';

    public function findByUtilisateurId(int $utilisateurId): ?Profil
    {
        $sql = "SELECT * FROM {$this->table} WHERE utilisateur_id = :utilisateur_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['utilisateur_id' => $utilisateurId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) {
            return null;
        }

        return $this->toObject($data);
    }

    public function createForUtilisateur(int $utilisateurId, string $typeProfil): Profil
    {
        $client = ($typeProfil === 'client') ? 1 : 0;
        $serviceCommercial = ($typeProfil === 'service_commercial') ? 1 : 0;
        
        $data = [
            'client' => $client,
            'service_commercial' => $serviceCommercial,
            'utilisateur_id' => $utilisateurId,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $id = $this->save($data);
        
        $profil = new Profil();
        $profil->setId($id)
            ->setClient((bool)$client)
            ->setServiceCommercial((bool)$serviceCommercial)
            ->setUtilisateurId($utilisateurId)
            ->setCreatedAt(new \DateTime());

        return $profil;
    }

    public function updateProfil(int $utilisateurId, string $typeProfil): bool
    {
        $client = ($typeProfil === 'client') ? 1 : 0;
        $serviceCommercial = ($typeProfil === 'service_commercial') ? 1 : 0;
        
        $sql = "UPDATE {$this->table} SET client = :client, service_commercial = :service_commercial WHERE utilisateur_id = :utilisateur_id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'client' => $client,
            'service_commercial' => $serviceCommercial,
            'utilisateur_id' => $utilisateurId
        ]);
    }

    public function findAllByType(string $typeProfil): array
    {
        $column = ($typeProfil === 'client') ? 'client' : 'service_commercial';
        
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $profils = [];
        foreach ($results as $data) {
            $profils[] = $this->toObject($data);
        }
        
        return $profils;
    }

    protected function toObject(array $data): object
    {
        $profil = new Profil();
        $profil->setId($data['id'])
            ->setClient((bool)$data['client'])
            ->setServiceCommercial((bool)$data['service_commercial'])
            ->setUtilisateurId($data['utilisateur_id']);
            
        if (isset($data['created_at'])) {
            $profil->setCreatedAt(new \DateTime($data['created_at']));
        }

        return $profil;
    }
}
