<?php

namespace App\Service;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PictureService
{
    public function __construct(private ParameterBagInterface $params)
    {
    }

    public function add(UploadedFile $picture, ?string $folder = '')
    {
        $fichier = md5(uniqid(rand(), true)) . '.webp';
        $picture_infos = getimagesize($picture);



        if ($picture_infos === false) {
            throw new Exception('Format d\'image incorrect');
        }

        $path = $this->params->get('images_directory') . $folder;

        $picture->move($path . '/', $fichier);

        return $fichier;
    }

    public function delete(string $fichier, ?string $folder = '')
    {


        $path = $this->params->get('images_directory') . $folder;


        $original = $path . '/' . $fichier;

        if (file_exists($original)) {
            unlink($original);
        }else{
            return false;
        }

        return false;
    }
}
