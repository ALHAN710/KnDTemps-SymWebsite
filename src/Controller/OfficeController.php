<?php

namespace App\Controller;

use App\Entity\Office;
use App\Form\OfficeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OfficeController extends AbstractController
{
    /**
     * @Route("/offices/home", name="offices_index")
     */
    public function index(): Response
    {
        $offices = $this->getUser()->getEnterprise()->getOffices();
        //dd($offices);
        return $this->render('office/index.html.twig', [
            'offices' => $offices,
        ]);
    }


    /**
     * Permet de créer un Poste
     *
     * @Route("/office/new", name = "office_create")
     * 
     * 
     * 
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $manager)
    { //@Security( "( is_granted('ROLE_STOCK_MANAGER') and user.getEnterprise().getIsActivated() == true ) " )
        $office = new Office();
        $office->setEnterprise($this->getUser()->getEnterprise());


        //Permet d'obtenir un constructeur de formulaire
        // Externaliser la création du formulaire avec la cmd php bin/console make:form
        //  instancier un form externe
        $form = $this->createForm(OfficeType::class, $office);
        $form->handleRequest($request);
        //dump($site);
        if ($form->isSubmitted() && $form->isValid()) {

            //$manager = $this->getDoctrine()->getManager();


            $manager->persist($office);
            $manager->flush();
            $this->addFlash(
                'success',
                "Le Poste <strong> {$office->getName()} </strong> a été enregistré avec succès !"
            );


            return $this->redirectToRoute('offices_index');
        }


        return $this->render(
            'office/new.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Permet d'afficher le formulaire d'édition d'un Poste
     *
     * @Route("/office/{id<\d+>}/edit", name="office_edit")
     * 
     * 
     * 
     * @return Response
     */
    public function edit(Office $office, Request $request, EntityManagerInterface $manager)
    { //@Security( "( is_granted('ROLE_STOCK_MANAGER') and office.getEnterprise() === user.getEnterprise() )" )

        //  instancier un form externe
        $form = $this->createForm(OfficeType::class, $office);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //$manager = $this->getDoctrine()->getManager();
            $manager->persist($office);
            $manager->flush();

            $this->addFlash(
                'success',
                "Les modifications du Poste <strong> {$office->getName()} </strong> ont été sauvegardées avec succès !"
            );

            return $this->redirectToRoute('offices_index');
        }

        return $this->render('office/edit.html.twig', [
            'form'       => $form->createView(),
        ]);
    }

    /**
     * Permet de supprimer un Poste
     * 
     * @Route("/office/{id}/delete", name="office_delete")
     *
     * 
     * 
     * @param Office $office
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function delete(Office $office, EntityManagerInterface $manager)
    { //@Security( "is_granted('ROLE_SUPER_ADMIN') or ( is_granted('ROLE_STOCK_MANAGER') and office.getEnterprise() === user.getEnterprise() )" ) 
        $enterprise = $office->getEnterprise();
        $enterprise->removeOffice($office);

        $manager->persist($enterprise);
        foreach ($office->getEmployees() as $employee) {
            $office->removeEmployee($employee);
        }
        $manager->remove($office);
        $manager->flush();

        $this->addFlash(
            'success',
            "La suppression du Poste <strong> {$office->getName()} </strong> a été effectuée avec succès !"
        );
        return $this->json([
            'code'     => 200,
            'name' => $enterprise->getSocialReason(),
            'message'    => "La suppression du Poste <strong> {$office->getName()} </strong> a été effectuée avec succès !"
        ], 200);



        //return $this->redirectToRoute("pointing_locations_index");
    }
}
