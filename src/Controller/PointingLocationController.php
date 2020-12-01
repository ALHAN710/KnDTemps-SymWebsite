<?php

namespace App\Controller;

use App\Entity\PointingLocation;
use App\Form\PointingLocationType;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\ApplicationController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PointingLocationController extends ApplicationController
{
    /**
     * Page d'accueil des zones de pointage
     * 
     * @Route("/pointing/locations/home", name="pointing_locations_index")
     */
    public function index(): Response
    {
        $pointingLocations = $this->getUser()->getEnterprise()->getPointingLocations();
        //dd($pointingLocations);
        return $this->render('pointing_location/index.html.twig', [
            'pointingLocations' => $pointingLocations,
        ]);
    }

    /**
     * Permet de créer une zone de pointage
     *
     * @Route("/pointing/location/new", name = "pointing_location_create")
     * 
     * 
     * 
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $manager)
    { //@Security( "( is_granted('ROLE_STOCK_MANAGER') and user.getEnterprise().getIsActivated() == true ) " )
        $pointingLocation = new PointingLocation();
        $pointingLocation->setEnterprise($this->getUser()->getEnterprise());


        //Permet d'obtenir un constructeur de formulaire
        // Externaliser la création du formulaire avec la cmd php bin/console make:form
        //  instancier un form externe
        $form = $this->createForm(PointingLocationType::class, $pointingLocation);
        $form->handleRequest($request);
        //dump($site);
        if ($form->isSubmitted() && $form->isValid()) {

            //$manager = $this->getDoctrine()->getManager();


            $manager->persist($pointingLocation);
            $manager->flush();
            $this->addFlash(
                'success',
                "Le Zone de Pointage <strong> {$pointingLocation->getName()} </strong> a été enregistré avec succès !"
            );


            return $this->redirectToRoute('pointing_locations_index');
        }


        return $this->render(
            'pointing_location/new.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Permet d'afficher le formulaire d'édition d'une Zone de Pointage
     *
     * @Route("pointing/location/{id<\d+>}/edit", name="pointing_location_edit")
     * 
     * 
     * 
     * @return Response
     */
    public function edit(PointingLocation $pointingLocation, Request $request, EntityManagerInterface $manager)
    { //@Security( "( is_granted('ROLE_STOCK_MANAGER') and pointingLocation.getEnterprise() === user.getEnterprise() )" )

        //  instancier un form externe
        $form = $this->createForm(PointingLocationType::class, $pointingLocation);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //$manager = $this->getDoctrine()->getManager();
            $manager->persist($pointingLocation);
            $manager->flush();

            $this->addFlash(
                'success',
                "Les modifications de la Zone de Pointage <strong> {$pointingLocation->getName()} </strong> ont été sauvegardées avec succès !"
            );

            return $this->redirectToRoute('pointing_locations_index');
        }

        return $this->render('pointing_location/edit.html.twig', [
            'form'       => $form->createView(),
        ]);
    }

    /**
     * Permet de supprimer une Zone de Pointage
     * 
     * @Route("/pointing/location/{id}/delete", name="pointing_location_delete")
     *
     * 
     * 
     * @param PointingLocation $pointingLocation
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function delete(PointingLocation $pointingLocation, EntityManagerInterface $manager)
    { //@Security( "is_granted('ROLE_SUPER_ADMIN') or ( is_granted('ROLE_STOCK_MANAGER') and pointingLocation.getEnterprise() === user.getEnterprise() )" ) 
        $enterprise = $pointingLocation->getEnterprise();
        $enterprise->removePointingLocation($pointingLocation);

        $manager->persist($enterprise);
        foreach ($pointingLocation->getUsers() as $user) {
            $pointingLocation->removeUser($user);
        }
        $manager->remove($pointingLocation);
        //$manager->flush();

        $this->addFlash(
            'success',
            "La suppression de la Zone de Pointage <strong> {$pointingLocation->getName()} </strong> a été effectuée avec succès !"
        );
        return $this->json([
            'code'     => 200,
            'name' => $enterprise->getSocialReason(),
            'message'    => "La suppression de la Zone de Pointage <strong> {$pointingLocation->getName()} </strong> a été effectuée avec succès !"
        ], 200);



        //return $this->redirectToRoute("pointing_locations_index");
    }
}
