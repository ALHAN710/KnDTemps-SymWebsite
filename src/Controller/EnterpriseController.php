<?php

namespace App\Controller;

use App\Entity\Enterprise;
use Cocur\Slugify\Slugify;
use App\Form\EnterpriseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class EnterpriseController extends AbstractController
{
    /**
     * @Route("/enterprise/{id<\d+>}/profile/account", name="enterprise_account_profile")
     * 
     * @Security( "(is_granted('ROLE_HIDE_ADMIN') or is_granted('ROLE_ADMIN')) and enterprise === user.getEnterprise() " )
     */
    public function index(Enterprise $enterprise, EntityManagerInterface $manager, Request $request): Response
    {
        $lastLogo = $enterprise->getLogo();
        $filesystem = new Filesystem();
        $slugify = new Slugify();

        //  instancier un form externe
        $form = $this->createForm(EnterpriseType::class, $enterprise, [
            //'entId'       => $this->getUser()->getEnterprise()->getId(),

        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // @var UploadedFile $logoFile 
            $logoFile = $form->get('logo')->getData();

            // this condition is needed because the 'logo' field is not required
            // so the Image file must be processed only when a file is uploaded
            if ($logoFile) {
                $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugify->slugify($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $logoFile->guessExtension();

                // Move the file to the directory where logos are stored
                try {
                    $logoFile->move(
                        $this->getParameter('logo_directory'),
                        $newFilename
                    );
                    $path = $this->getParameter('logo_directory') . '/' . $lastLogo;
                    if ($lastLogo != NULL) $filesystem->remove($path);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $enterprise->setLogo($newFilename);
            }


            $manager->persist($enterprise);
            $manager->flush();

            $this->addFlash(
                'success',
                "Les Modifications du profil ont été enregistrées avec succès !."
            );
        }
        return $this->render('enterprise/account.html.twig', [
            'enterprise' => $enterprise,
            'form'       => $form->createView(),
        ]);
    }
}
