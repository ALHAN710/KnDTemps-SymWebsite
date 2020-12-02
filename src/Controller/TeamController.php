<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TeamController extends AbstractController
{
    /**
     * @Route("/teams/home", name="teams_index")
     */
    public function index(): Response
    {
        $teams = $this->getUser()->getEnterprise()->getTeams();
        //dd($offices);
        return $this->render('team/index.html.twig', [
            'teams' => $teams,
        ]);
    }

    /**
     * Permet de créer une Équipe
     *
     * @Route("/team/new", name = "team_create")
     * 
     * 
     * 
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $manager)
    { //@Security( "( is_granted('ROLE_STOCK_MANAGER') and user.getEnterprise().getIsActivated() == true ) " )
        $team = new Team();
        $team->setEnterprise($this->getUser()->getEnterprise());


        //Permet d'obtenir un constructeur de formulaire
        // Externaliser la création du formulaire avec la cmd php bin/console make:form
        //  instancier un form externe
        $form = $this->createForm(TeamType::class, $team, [
            'entId' => $this->getUser()->getEnterprise()->getId(),
            'isEdit' => false,
        ]);
        $form->handleRequest($request);
        //dump($site);
        if ($form->isSubmitted() && $form->isValid()) {

            //$manager = $this->getDoctrine()->getManager();


            $manager->persist($team);
            $manager->flush();
            $this->addFlash(
                'success',
                "L'équipe <strong> {$team->getName()} </strong> a été enregistré avec succès !"
            );


            return $this->redirectToRoute('teams_index');
        }


        return $this->render(
            'team/new.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Permet d'afficher le formulaire d'édition d'une Équipe
     *
     * @Route("/team/{id<\d+>}/edit", name="team_edit")
     * 
     * 
     * 
     * @return Response
     */
    public function edit(Team $team, Request $request, EntityManagerInterface $manager)
    { //@Security( "( is_granted('ROLE_STOCK_MANAGER') and team.getEnterprise() === user.getEnterprise() )" )

        //  instancier un form externe
        $form = $this->createForm(TeamType::class, $team, [
            'entId' => $this->getUser()->getEnterprise()->getId(),

        ]);
        // dump($team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // foreach ($team->getUsers() as $user) {
            //     $user->setTeam($team);
            //     $manager->persist($user);
            //     dump($user);
            // }
            // dump($team);
            // die();
            $manager->persist($team);
            $manager->flush();

            $this->addFlash(
                'success',
                "Les modifications de l'Équipe <strong> {$team->getName()} </strong> ont été sauvegardées avec succès !"
            );

            return $this->redirectToRoute('teams_index');
        }

        return $this->render('team/edit.html.twig', [
            'form'       => $form->createView(),
        ]);
    }

    /**
     * Permet de supprimer une Équipe
     * 
     * @Route("/team/{id}/delete", name="team_delete")
     *
     * 
     * 
     * @param Team $team
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function delete(Team $team, EntityManagerInterface $manager)
    { //@Security( "is_granted('ROLE_SUPER_ADMIN') or ( is_granted('ROLE_STOCK_MANAGER') and team.getEnterprise() === user.getEnterprise() )" ) 
        $enterprise = $team->getEnterprise();
        $enterprise->removeTeam($team);

        $manager->persist($enterprise);
        foreach ($team->getUsers() as $user) {
            $team->removeUser($user);
        }
        $manager->remove($team);
        $manager->flush();

        $this->addFlash(
            'success',
            "La suppression de l'Équipe <strong> {$team->getName()} </strong> a été effectuée avec succès !"
        );
        return $this->json([
            'code'     => 200,
            'name' => $enterprise->getSocialReason(),
            'message'    => "La suppression de l'Équipe <strong> {$team->getName()} </strong> a été effectuée avec succès !"
        ], 200);



        //return $this->redirectToRoute("teamss_index");
    }
}
