<?php

namespace App\Controller\Admin;

use App\Entity\Subscription;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SubscriptionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminProductController extends AbstractController
{
    /**
     * @Route("/admin/products", name="admin_products_index")
     * 
     */
    public function index(SubscriptionRepository $subscriptionRepo)
    {
        $subscriptions = $subscriptionRepo->findAll();
        return $this->render('admin/product/index_product.html.twig', [
            'subscriptions'    => $subscriptions,

        ]);
    }

    /**
     * Permet de créer un Produit
     *
     * @Route("/admin/product/new", name = "admin_product_create")
      
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $manager)
    { //  @IsGranted("ROLE_STOCK_MANAGER")
        $subscription = new Subscription();

        //Permet d'obtenir un constructeur de formulaire
        // Externaliser la création du formulaire avec la cmd php bin/console make:form

        //  instancier un form externe
        $form = $this->createForm(SubscriptionType::class, $subscription);
        $form->handleRequest($request);
        //dump($site);
        $valid = true;
        if ($form->isSubmitted() && $form->isValid()) {


            $manager->persist($subscription);
            $manager->flush();
            $this->addFlash(
                'success',
                "L'abonnement <strong> {$subscription->getName()} </strong> a été enregistré avec succès !"
            );

            return $this->redirectToRoute('admin_products_index');
        }


        return $this->render(
            'admin/product/new.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Permet d'afficher le formulaire d'édition d'un product
     *
     * @Route("/admin/product/{id<\d+>}/edit", name="admin_product_edit")
     * 
     * 
     * 
     * @return Response
     */
    public function edit(Subscription $product, Request $request, EntityManagerInterface $manager)
    { //@Security("is_granted('ROLE_STOCK_MANAGER')", message = "Vous n'avez pas le droit d'accéder à cette ressource")


        //Permet d'obtenir un constructeur de formulaire
        // Externaliser la création du formulaire avec la cmd php bin/console make:form

        //  instancier un form externe
        $form = $this->createForm(ProductType::class, $product); //  instancier un form externe

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //$manager = $this->getDoctrine()->getManager();
            $manager->persist($product);
            $manager->flush();

            $this->addFlash(
                'success',
                "Les modifications de l'abonnement <strong> {$product->getName()} </strong> ont été enregistrées avec succès !"
            );

            return $this->redirectToRoute('admin_products_index');
        }

        return $this->render('admin/product/edit.html.twig', [
            'form'        => $form->createView(),

        ]);
    }

    /**
     * Permet de supprimer un product
     * 
     * @Route("/admin/product/{id}/delete", name="admin_product_delete")
     *
     * @param Subscription $subscription
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function delete(Subscription $subscription, EntityManagerInterface $manager)
    { // @IsGranted("ROLE_STOCK_MANAGER"), 

        $manager->remove($subscription);
        $manager->flush();

        $this->addFlash(
            'success',
            "La suppression de l'abonnement <strong> {$subscription->getName()} </strong> a été effectuée avec succès !"
        );

        return $this->redirectToRoute("admin_products_index");
    }
}
