<?php

namespace App\Entity;

use App\Core\Abstract\AbstractEntity;
use App\Core\Middlewares\CryptPassword;
use App\Core\Middlewares\DecryptPassword;

class Utilisateur extends AbstractEntity
{
    private ?int $id = null;
    private string $nom;
    private string $login;
    private string $motDePasse;
    private string $prenom;
    private string $numeroTelephone;
    private string $numeroCNI;
    private string $adresse;
    private string $photoRecto;
    private string $photoVerso;

    public function __construct()
    {
        // Constructeur vide pour permettre l'hydratation
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;
        return $this;
    }

    public function getMotDePasse(): string
    {
        return $this->motDePasse;
    }

    public function setMotDePasse(string $motDePasse): self
    {
        $this->motDePasse = CryptPassword::hashPassword($motDePasse);
        return $this;
    }

    public function setMotDePasseHash(string $hash): self
    {
        $this->motDePasse = $hash;
        return $this;
    }

    public function verifyPassword(string $password): bool
    {
        return DecryptPassword::verifyPassword($password, $this->motDePasse);
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getNumeroTelephone(): string
    {
        return $this->numeroTelephone;
    }

    public function setNumeroTelephone(string $numeroTelephone): self
    {
        $this->numeroTelephone = $numeroTelephone;
        return $this;
    }

    public function getNumeroCNI(): string
    {
        return $this->numeroCNI;
    }

    public function setNumeroCNI(string $numeroCNI): self
    {
        $this->numeroCNI = $numeroCNI;
        return $this;
    }

    public function getAdresse(): string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getPhotoRecto(): string
    {
        return $this->photoRecto;
    }

    public function setPhotoRecto(string $photoRecto): self
    {
        $this->photoRecto = $photoRecto;
        return $this;
    }

    public function getPhotoVerso(): string
    {
        return $this->photoVerso;
    }

    public function setPhotoVerso(string $photoVerso): self
    {
        $this->photoVerso = $photoVerso;
        return $this;
    }

    public function getNomComplet(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public static function toObject(array $data): object
    {
        $utilisateur = new self();
        $utilisateur->setNom($data['nom'] ?? '')
            ->setLogin($data['login'] ?? '')
            ->setMotDePasse($data['motDePasse'] ?? '')
            ->setPrenom($data['prenom'] ?? '')
            ->setNumeroTelephone($data['numeroTelephone'] ?? '')
            ->setNumeroCNI($data['numeroCNI'] ?? '')
            ->setAdresse($data['adresse'] ?? '')
            ->setPhotoRecto($data['photoRecto'] ?? '')
            ->setPhotoVerso($data['photoVerso'] ?? '');

        return $utilisateur;
    }

    public static function toArray(object $object): array
    {
        if (!$object instanceof self) {
            throw new \InvalidArgumentException("L'objet doit être une instance de Utilisateur");
        }

        return [
            'nom' => $object->getNom(),
            'login' => $object->getLogin(),
            'motDePasse' => $object->getMotDePasse(),
            'prenom' => $object->getPrenom(),
            'numeroTelephone' => $object->getNumeroTelephone(),
            'numeroCNI' => $object->getNumeroCNI(),
            'adresse' => $object->getAdresse(),
            'photoRecto' => $object->getPhotoRecto(),
            'photoVerso' => $object->getPhotoVerso(),
        ];
    }
}
