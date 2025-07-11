<?php

namespace App\Core;

class FileUpload
{
    private string $uploadDir;
    private array $allowedExtensions;
    private int $maxFileSize;

    public function __construct(
        string $uploadDir = 'public/uploads/',
        array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'],
        int $maxFileSize = 5242880 // 5MB par défaut
    ) {
        $this->uploadDir = rtrim($uploadDir, '/') . '/';
        $this->allowedExtensions = $allowedExtensions;
        $this->maxFileSize = $maxFileSize;
        
        // Créer le dossier d'upload s'il n'existe pas
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Upload un fichier
     */
    public function upload(array $file, string $prefix = ''): array
    {
        // Vérifier si le fichier a été uploadé sans erreur
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'error' => $this->getUploadErrorMessage($file['error'] ?? UPLOAD_ERR_NO_FILE),
                'filename' => null
            ];
        }

        // Vérifier la taille du fichier
        if ($file['size'] > $this->maxFileSize) {
            return [
                'success' => false,
                'error' => 'Le fichier est trop volumineux. Taille maximale autorisée: ' . $this->formatBytes($this->maxFileSize),
                'filename' => null
            ];
        }

        // Vérifier l'extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            return [
                'success' => false,
                'error' => 'Extension non autorisée. Extensions acceptées: ' . implode(', ', $this->allowedExtensions),
                'filename' => null
            ];
        }

        // Vérifier que c'est vraiment une image
        if (!$this->isValidImage($file['tmp_name'])) {
            return [
                'success' => false,
                'error' => 'Le fichier n\'est pas une image valide',
                'filename' => null
            ];
        }

        // Générer un nom de fichier unique
        $filename = $this->generateUniqueFilename($prefix, $extension);
        $filepath = $this->uploadDir . $filename;

        // Déplacer le fichier
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'error' => null,
                'filename' => $filename,
                'filepath' => $filepath,
                'size' => $file['size'],
                'original_name' => $file['name']
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Erreur lors du déplacement du fichier',
                'filename' => null
            ];
        }
    }

    /**
     * Upload multiple fichiers
     */
    public function uploadMultiple(array $files, string $prefix = ''): array
    {
        $results = [];
        
        foreach ($files as $key => $file) {
            $filePrefix = $prefix ? $prefix . '_' . $key : $key;
            $results[$key] = $this->upload($file, $filePrefix);
        }
        
        return $results;
    }

    /**
     * Supprimer un fichier uploadé
     */
    public function delete(string $filename): bool
    {
        $filepath = $this->uploadDir . $filename;
        
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        
        return false;
    }

    /**
     * Vérifier si un fichier est une image valide
     */
    private function isValidImage(string $tmpName): bool
    {
        $imageInfo = getimagesize($tmpName);
        return $imageInfo !== false;
    }

    /**
     * Générer un nom de fichier unique
     */
    private function generateUniqueFilename(string $prefix, string $extension): string
    {
        $timestamp = time();
        $uniqueId = uniqid();
        
        if ($prefix) {
            return $prefix . '_' . $timestamp . '_' . $uniqueId . '.' . $extension;
        }
        
        return $timestamp . '_' . $uniqueId . '.' . $extension;
    }

    /**
     * Obtenir le message d'erreur d'upload
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'Le fichier dépasse la taille maximale autorisée par PHP';
            case UPLOAD_ERR_FORM_SIZE:
                return 'Le fichier dépasse la taille maximale autorisée par le formulaire';
            case UPLOAD_ERR_PARTIAL:
                return 'Le fichier n\'a été que partiellement uploadé';
            case UPLOAD_ERR_NO_FILE:
                return 'Aucun fichier n\'a été uploadé';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Dossier temporaire manquant';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Échec de l\'écriture du fichier sur le disque';
            case UPLOAD_ERR_EXTENSION:
                return 'Une extension PHP a arrêté l\'upload';
            default:
                return 'Erreur d\'upload inconnue';
        }
    }

    /**
     * Formater la taille en bytes de manière lisible
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Obtenir la liste des extensions autorisées
     */
    public function getAllowedExtensions(): array
    {
        return $this->allowedExtensions;
    }

    /**
     * Modifier les extensions autorisées
     */
    public function setAllowedExtensions(array $extensions): self
    {
        $this->allowedExtensions = $extensions;
        return $this;
    }

    /**
     * Obtenir la taille maximale autorisée
     */
    public function getMaxFileSize(): int
    {
        return $this->maxFileSize;
    }

    /**
     * Modifier la taille maximale autorisée
     */
    public function setMaxFileSize(int $size): self
    {
        $this->maxFileSize = $size;
        return $this;
    }

    /**
     * Obtenir le répertoire d'upload
     */
    public function getUploadDir(): string
    {
        return $this->uploadDir;
    }
}
