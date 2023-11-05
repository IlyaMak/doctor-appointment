<?php

namespace App\Service;

use ImageKit\ImageKit;
use ImageKit\Utils\Response;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageKitService
{
    public function __construct(
        #[Autowire(env: 'IMAGEKIT_PUBLIC_KEY')]
        private string $publicKey,
        #[Autowire(env: 'IMAGEKIT_PRIVATE_KEY')]
        private string $privateKey,
        #[Autowire(env: 'IMAGEKIT_URL')]
        private string $url
    ) {
    }

    public function uploadImage(string $imagePath, UploadedFile $uploadedFile): Response
    {
        $imageKit = new ImageKit(
            $this->publicKey,
            $this->privateKey,
            $this->url
        );

        $response = $imageKit->uploadFile(
            [
                'file' => base64_encode($uploadedFile->getContent()),
                'fileName' => $imagePath
            ]
        );

        return $response;
    }
}
