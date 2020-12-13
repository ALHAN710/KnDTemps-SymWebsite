<?php

namespace App\Controller\Admin;

use DateTime;
use DateTimeZone;
use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Form\AdminSubscriptionType;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\ApplicationController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Notifier\TexterInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminInvoiceController extends ApplicationController
{
    /**
     * @Route("/admin/invoices/{type<[a-z]+>}", name="admin_invoices")
     */
    public function index($type, EntityManagerInterface $manager)
    {
        $invoices = [];
        $enterprises = $manager->getRepository('App:Enterprise')->findAll();
        if ($type === 'all') {
            $invoices = $manager->getRepository('App:Invoice')->findAll();
        }
        $tarifs = null;
        $subscriptions = $manager->getRepository('App:Subscription')->findAll();
        foreach ($subscriptions as $subscription) {
            $tarifs['' . $subscription->getId()] = $subscription->getTarifs();
        }

        $form = $this->createForm(AdminSubscriptionType::class, $subscription);

        //dump($enterprises);
        return $this->render('admin/invoices/index_invoices.html.twig', [
            'invoices'    => $invoices,
            'enterprises' => $enterprises,
            'type'        => $type,
            'form'        => $form->createView(),
            'tarifs'      => $tarifs,
        ]);
    }

    /**
     * Permet d'enregistrer une nouvelle commande d'abonnement
     * 
     * @Route("/order/subscription", name="order_subscription")
     *
     * @Security( "is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_ADMIN')" )
     * 
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function orderASubscription(Request $request, EntityManagerInterface $manager)
    {
        $paramJSON = $this->getJSONRequest($request->getContent());
        if ((array_key_exists("subscriptionName", $paramJSON) && !empty($paramJSON['subscriptionName'])) && (array_key_exists("duration", $paramJSON) && !empty($paramJSON['duration'])) && (array_key_exists("price", $paramJSON) && !empty($paramJSON['price'])) && (array_key_exists("ent", $paramJSON) && !empty($paramJSON['ent']))) {
            $enterprise = $manager->getRepository('App:Enterprise')->findOneBy(['id' => $paramJSON['ent']]);
            if ($enterprise) {
                //Création d'une nouvelle facture
                $invoice = new Invoice();
                $invoice->setEnterprise($enterprise)
                    ->setUser($this->getUser())
                    ->setDuration($paramJSON['duration'])
                    ->setFixReduction(0)
                    ->setAdvancePayment(0);

                //Création de l'article Abonnement pour la facture 
                $invoiceItem = new InvoiceItem();
                $invoiceItem->setDesignation($paramJSON['subscriptionName'])
                    ->setPU($paramJSON['price'])
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

                //Envoi d'un mail d'accusé de réception de la commande au client

                //dump($this->getUser());

                return $this->json([
                    'code' => 200,
                    //'message' => 'Empty Array or Not existss !',
                ], 200);
            }
        }

        return $this->json([
            'code' => 403,
            'message' => 'Empty Array or Not existss !',
        ], 403);
    }

    /**
     * Gestion des commandes marquées livrée et/ou payée
     * 
     * @Route("/admin/invoices/change/status", name="invoices_change_status")
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return JsonResponse
     */
    public function changeStatus(Request $request, EntityManagerInterface $manager, MailerInterface $mailer): JsonResponse
    { // , TexterInterface $texter
        $paramJSON = $this->getJSONRequest($request->getContent());
        if (array_key_exists("invoiceDeliveredIds", $paramJSON) && array_key_exists("invoicePaidIds", $paramJSON)) {
            if (!empty($paramJSON['invoiceDeliveredIds']) || !empty($paramJSON['invoicePaidIds'])) {
                if (!empty($paramJSON['invoiceDeliveredIds'])) {
                    foreach ($paramJSON['invoiceDeliveredIds'] as $Id) {
                        $invoice = $manager->getRepository('App:Invoice')->findOneBy(['id' => intval($Id)]);
                        //dump($invoice);
                        if ($invoice) {
                            $date = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('Africa/Douala'));
                            $invoice->setDeliveryStatus(true)
                                ->setDeliverAt($date);
                            if ($invoice->getDeliveryStatus() && $invoice->getPaymentStatus()) {
                                $invoice->setCompletedStatus(true);
                                $invoice->setCompletedAt($date);
                            }
                            $manager->persist($invoice);
                        }
                    }
                }
                if (!empty($paramJSON['invoicePaidIds'])) {
                    foreach ($paramJSON['invoicePaidIds'] as $Id) {
                        $invoice = $manager->getRepository('App:Invoice')->findOneBy(['id' => intval($Id)]);
                        if ($invoice) {
                            $date = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('Africa/Douala'));
                            $invoice->setPaymentStatus(true)
                                ->setPayAt($date)
                                ->setAdvancePayment($invoice->getAmountNetToPaid())
                                ->setDeliveryStatus(true)
                                ->setDeliverAt($date)
                                ->setCompletedStatus(true)
                                ->setCompletedAt($date);

                            $invoice->getEnterprise()->setSubscribeAt($date);
                            $manager->persist($invoice->getEnterprise());
                            // if ($invoice->getDeliveryStatus() && $invoice->getPaymentStatus()) {
                            // }
                            $manager->persist($invoice);

                            foreach ($invoice->getInvoiceItems() as $invoiceItem) {
                                $subscription = $manager->getRepository('App:Subscription')->findOneBy(['name' => $invoiceItem->getDesignation()]);
                                if ($subscription) {
                                    $enterprise = $invoice->getEnterprise();
                                    $enterprise->setSubscription($subscription)
                                        ->setSubscriptionDuration($invoice->getDuration())
                                        ->setIsActivated(true);

                                    $manager->persist($enterprise);

                                    $enterprise = $manager->getRepository('App:Enterprise')->findOneBy([
                                        'socialReason' => 'KnD Technologies'
                                    ]);
                                    $cms = $manager->createQuery("SELECT cms
                                     FROM App\Entity\Invoice cms
                                     JOIN cms.user u
                                     JOIN u.enterprise e
                                     WHERE cms.createdAt LIKE :dat
                                     AND e.id = :entId

                                    ")
                                        ->setParameters([
                                            'dat' => '%' . $invoice->getCreatedAt()->format('Y-m') . '%',
                                            'entId' => $enterprise->getId(),
                                        ])
                                        ->getResult();
                                    $numOrder = 0;
                                    $str = '';
                                    foreach ($cms as $key => $value) {
                                        if ($value === $invoice) $numOrder = $key + 1;
                                    }
                                    //dump($numOrder);

                                    //Envoi d'un mail pour réabonnement au client
                                    $object = 'Facture disponible dans votre compte client';
                                    $message = "Chère cliente, cher client,

Nous vous remercions pour votre commande numéro {$str}{$invoice->getCreatedAt()->format("m")}{$invoice->getCreatedAt()->format("y")}. La facture associée, référence FR{$numOrder}{$invoice->getCreatedAt()->format("m")}{$invoice->getCreatedAt()->format("y")}, d'un montant de {$invoice->getAmountNetToPaid()} XAF, a été éditée :

https://www.kndtemps.com/invoice/{$invoice->getId()}/print

Vous pouvez consulter cette dernière depuis votre compte client en cliquant sur paramètres -> mon compte, puis sur « Mes Factures ».

Nous vous remercions pour la confiance que vous accordez à KnD Temps et restons à votre disposition.

À bientôt sur notre site !
Cordialement,

Votre Service client KnD Temps"; //Pour nous contacter : https://www.ovh.com/fr/support/

                                    foreach ($enterprise->getUsers() as $user) {
                                        if ($user->getRoles()[0] === 'ROLE_ADMIN') {
                                            $this->sendEmail($mailer, $user->getEmail(), $object, $message);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $manager->flush();
                return $this->json([
                    'code' => 200,
                    //'Delivered' => $paramJSON['invoiceDeliveredIds'],
                    'Paid' => $paramJSON['invoicePaidIds'],
                ], 200);
            }
        }
        return $this->json([
            'code' => 403,
            'message' => 'Empty Array or Not existss !',
        ], 403);
    }

    /**
     * Permet d'afficher la facture d'un client pour impression
     * 
     * @Route("/invoice/{id}/print", name="admin_invoice_print")
     * 
     *  @Security( "is_granted('ROLE_SUPER_ADMIN') or ( is_granted('ROLE_ADMIN') and invoice.getEnterprise() == user.getEnterprise() )" )
     *
     * @param Invoice $invoice
     * @return void
     */
    public function printInvoice(Invoice $invoice, EntityManagerInterface $manager)
    { //@IsGranted("ROLE_USER")
        //$inventories = $inventoryRepo->findAll();
        //$date = $invoice->getCreatedAt();
        $enterprise = $manager->getRepository('App:Enterprise')->findOneBy([
            'socialReason' => 'KnD Technologies'
        ]);
        $inv = $manager->createQuery("SELECT inv
                                     FROM App\Entity\Invoice inv
                                     WHERE inv.createdAt LIKE :dat
                                     
                                    ")
            ->setParameters([
                'dat' => '%' . $invoice->getCreatedAt()->format('Y-m') . '%',
            ])
            ->getResult();
        $numOrder = 0;
        $str = '';
        foreach ($inv as $key => $value) {
            if ($value === $invoice) $numOrder = $key + 1;
        }
        //dump($numOrder);

        $str .= $numOrder;

        return $this->render('admin/invoices/print_invoice.html.twig', [
            'invoice'  => $invoice,
            'numOrder' => $str,
        ]);
    }
}
