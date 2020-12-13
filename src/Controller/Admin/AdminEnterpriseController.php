<?php

namespace App\Controller\Admin;

use App\Entity\Invoice;
use App\Entity\Enterprise;
use App\Entity\InvoiceItem;
use App\Form\EnterpriseType;
use App\Form\AdminEnterpriseType;
use App\Form\AdminSubscriptionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminEnterpriseController extends AbstractController
{
    /**
     * @Route("/admin/clients/home", name="admin_enterprises_index")
     */
    public function index(EntityManagerInterface $manager)
    {
        $tarifs = null;
        $subscription = null;
        $subscriptions = $manager->getRepository('App:Subscription')->findAll();
        foreach ($subscriptions as $subscription_) {
            $tarifs['' . $subscription_->getId()] = $subscription_->getTarifs();
            $subscription = $subscription_;
        }
        //dump($tarifs);
        //  instancier un form externe
        $form = $this->createForm(AdminSubscriptionType::class, $subscription);
        //$form->handleRequest($request);

        $enterprises = $manager->getRepository('App:Enterprise')->findAll();
        return $this->render('admin/enterprises_customer/index_enterprises.html.twig', [
            'enterprises' => $enterprises,
            'form'        => $form->createView(),
            'tarifs'      => $tarifs,
        ]);
    }

    /**
     * Permet de créer un Client Entreprise
     *
     * @Route("/enterprise/new", name = "admin_enterprise_create")
     * 
     * @Security( "is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_SELLER')" )
     * 
     * @return Response
     */
    public function create(Request $request, EntityManagerInterface $manager)
    { // @IsGranted("ROLE_SUPER_ADMIN")
        $enterprise = new Enterprise();

        $tarifs = null;
        $subscriptions = $manager->getRepository('App:Subscription')->findAll();
        foreach ($subscriptions as $subscription) {
            $tarifs['' . $subscription->getId()] = $subscription->getTarifs();
        }
        //dump($tarifs);
        //  instancier un form externe
        $form = $this->createForm(AdminEnterpriseType::class, $enterprise);
        $form->handleRequest($request);
        //dump($site);

        if ($form->isSubmitted() && $form->isValid()) {

            //Création d'une nouvelle facture
            $invoice = new Invoice();
            $invoice->setEnterprise($enterprise)
                ->setUser($this->getUser())
                ->setDuration($enterprise->getSubscriptionDuration())
                ->setFixReduction(0)
                ->setAdvancePayment(0);

            //Création de l'article Abonnement pour la facture 
            $invoiceItem = new InvoiceItem();
            $invoiceItem->setDesignation($enterprise->getSubscription()->getName())
                ->setPU($enterprise->getSubscription()->getTarifs()[$enterprise->getSubscriptionDuration()])
                ->setQuantity(1)
                ->setRemise(0);

            //Gestion des doublons d'articles dans la BDD
            $invoiceItem_ = $manager->getRepository('App:InvoiceItem')->findOneBy([
                'designation' => $invoiceItem->getDesignation(),
                'pu' => $invoiceItem->getPU(),
                'quantity' => $invoiceItem->getQuantity(),
                'remise'   => $invoiceItem->getRemise()
            ]);
            if (!empty($invoiceItem_)) { //Si il existe
                //dump('invoiceItem exists with id = ' . $invoiceItem_->getId());
                //$invoiceItem = $invoiceItem_;
                // $invoiceItem_->addinvoice($invoice);
                // $invoice->addinvoiceItem($invoiceItem_);
                // $invoice->removeinvoiceItem($invoiceItem);
                $invoiceItem = $invoiceItem_;
            }

            $invoiceItem->addInvoice($invoice);
            $invoice->addInvoiceItem($invoiceItem);
            $enterprise->addInvoice($invoice);

            //$manager->persist($businessContact);
            $manager->persist($invoiceItem);
            $manager->persist($invoice);


            //$manager = $this->getDoctrine()->getManager();
            $manager->persist($enterprise);
            $manager->flush();

            $this->addFlash(
                'success',
                "Le Client <strong> {$enterprise->getSocialReason()} </strong> a été crée avec succès !"
            );

            return $this->redirectToRoute('admin_enterprises_index');
        }


        return $this->render('admin/enterprises_customer/new.html.twig', [
            'form'   => $form->createView(),
            'tarifs' => $tarifs,
        ]);
    }

    /**
     * Permet d'afficher le formulaire d'édition d'un client Entreprise
     *
     * @Route("/admin/enterprise/{id<\d+>}/edit", name="admin_enterprise_edit")
     * 
     * @return Response
     */
    public function edit(Enterprise $enterprise, Request $request, EntityManagerInterface $manager)
    { //@Security("is_granted('ROLE_SUPER_ADMIN')", message = "Vous n'avez pas le droit d'accéder à cette ressource")
        //@IsGranted("ROLE_SUPER_ADMIN")

        /*$tarifs = null;
        $subscriptions = $manager->getRepository('App:Subscription')->findAll();
        foreach ($subscriptions as $subscription) {
            $tarifs['' . $subscription->getId()] = $subscription->getTarifs();
        }*/
        //  instancier un form externe
        $form = $this->createForm(AdminEnterpriseType::class, $enterprise, ['isEdit' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //$manager = $this->getDoctrine()->getManager();
            $manager->persist($enterprise);
            $manager->flush();

            $this->addFlash(
                'success',
                "Les modifications du Client <strong> {$enterprise->getSocialReason()} </strong> ont été sauvegardées avec succès !"
            );

            return $this->redirectToRoute('admin_enterprises_index');
        }

        return $this->render('admin/enterprises_customer/edit.html.twig', [
            'form'         => $form->createView(),
            'enterprise'   => $enterprise,
            //'tarifs'       => $tarifs,
        ]);
    }

    /**
     * Permet de supprimer un Client
     * 
     * @Route("/admin/enterprise/{id<\d+>}/delete", name="admin_enterprise_delete")
     *
     * @IsGranted("ROLE_SUPER_ADMIN")
     * 
     * @param Enterprise $enterprise
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function delete(Enterprise $enterprise, EntityManagerInterface $manager)
    {
        $manager->remove($enterprise);
        $manager->flush();

        $this->addFlash(
            'success',
            "La suppression du Client <strong> {$enterprise->getSocialReason()} </strong> a été effectuée avec succès !"
        );

        return $this->redirectToRoute("admin_enterprises_index");
    }
}
