<?php

namespace App\Controller;

use App\Entity\Photo;
use App\Service\PhotoVisibilityService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted("ROLE_USER")]
class MyController extends AbstractController
{

    #[Route('/my/photos', name: 'my_photos')]
    public function index(EntityManagerInterface $em)
    {
        $myPhotos = $em->getRepository(Photo::class)->findBy(['user' => $this->getUser()]);

        return $this->render('my/index.html.twig', [
            'myPhotos' => $myPhotos
        ]);
    }

    #[Route('/my/photos/set_visibility/{id}/{visibility}', name: 'my_photos_set_visibility')]
    public function myPhotosChangeVisibility(PhotoVisibilityService $photoVisibilityService, int $id, bool $visibility)
    {
        $messages = [
            '1' => 'publiczne',
            '0' => 'prywatne'
        ];

        if ($photoVisibilityService->makeVisibility($id, $visibility))
            $this->addFlash('success', 'Ustawiono jako '.$messages[$visibility].'.');
        else
            $this->addFlash('error', 'Wystąpił problem przy ustawianiu jako '.$messages[$visibility].'.');

        return $this->redirectToRoute('my_photos');
    }

    #[Route('/my/photos/remove/{id}', name: 'my_photos_remove')]
    public function myPhotoRemove(int $id, EntityManagerInterface $em)
    {
        $myPhoto = $em->getRepository(Photo::class)->find($id);


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

        return $this->redirectToRoute('my_photos');
    }

}