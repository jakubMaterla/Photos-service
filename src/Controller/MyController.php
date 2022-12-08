<?php

namespace App\Controller;

use App\Entity\Photo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Annotation\Route;

class MyController extends AbstractController
{

    public function index()
    {

    }

    #[Route('/my/photos/set_private/{id}', name: 'my_photos_set_as_private')]
    public function myPhotosSetAsPrivate(int $id, EntityManagerInterface $em)
    {
        $myPhoto = $em->getRepository(Photo::class)->find($id);

        if ($this->getUser() == $myPhoto->getUser())
        {
            try {
                $myPhoto->setIsPublic(0);
                $em->persist($myPhoto);
                $em->flush();
                $this->addFlash('success', 'Ustawiono jako prywatne.');

            } catch (\Exception $e)
            {
                $this->addFlash('error', 'Wystąpił problem przy ustawianiu jako prywatne.');
            }
        }
        else
        {
            $this->addFlash('error', 'Nie jesteś włąścicielem tego zdjęcia.');
        }

        return $this->redirectToRoute('latest_photos');
    }

    #[Route('/my/photos/set_public/{id}', name: 'my_photos_set_as_public')]
    public function myPhotosSetAsPublic(int $id, EntityManagerInterface $em)
    {
        $myPhoto = $em->getRepository(Photo::class)->find($id);

        if ($this->getUser() == $myPhoto->getUser())
        {
            try {
                $myPhoto->setIsPublic(1);
                $em->persist($myPhoto);
                $em->flush();
                $this->addFlash('success', 'Ustawiono jako publiczne.');

            } catch (\Exception $e)
            {
                $this->addFlash('error', 'Wystąpił problem przy ustawianiu jako publiczne.');
            }
        }
        else
        {
            $this->addFlash('error', 'Nie jesteś włąścicielem tego zdjęcia.');
        }

        return $this->redirectToRoute('latest_photos');
    }
    #[Route('/my/photos/remove/{id}', name: 'my_photos_remove')]
    public function myPhotoRemove(int $id, EntityManagerInterface $em)
    {
        $myPhoto = $em->getRepository(Photo::class)->find($id);

        if ($this->getUser() == $myPhoto->getUser()) {
            $fileManager = new Filesystem();
            $fileManager->remove('images/hosting/'.$myPhoto->getFilename());

            if ($fileManager->exists('images/hosting/'.$myPhoto->getFilename()))
            {
                $this->addFlash('error', 'Nie udało się usunąć zdjęcia');
            }
            else
            {
                $em->remove($myPhoto);
                $em->flush();
                $this->addFlash('success', 'Usunięto zdjęcie');
            }
        }
        return $this->redirectToRoute('latest_photos');
    }

}