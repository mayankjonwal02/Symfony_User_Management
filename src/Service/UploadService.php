<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;



class UploadService
{
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function uploadFile(UploadedFile $file): void
    {
        $csvData = file($file->getPathname());
        $header = null;
        
        foreach ($csvData as $line) {
            $data = str_getcsv($line);

            if (!$header) {
                $header = $data;
                continue;
            }

            // Assuming CSV structure: name,email,username,address,role
            $user = new User();
            $user->setName($data[0]);
            $user->setEmail($data[1]);
            $user->setUsername($data[2]);
            $user->setAddress($data[3]);
            $user->setRole($data[4]);

            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();
    }
}