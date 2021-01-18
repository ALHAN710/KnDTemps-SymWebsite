<?php

namespace App\Controller;

use DateTime;
use DateInterval;
use App\Entity\Pointing;
use App\Form\PointingType;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\ApplicationController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class PointingsController extends ApplicationController
{
    /**
     * @Route("/pointing/home", name="pointings")
     * 
     * @Security( "is_granted('ROLE_USER')" )
     * 
     */
    public function index(EntityManagerInterface $manager): Response
    {
        $date = new DateTime('now');

        //Récupération du nbre de pointage du jour de l'utilisateur connecté
        $currentPointing = $manager->createQuery("SELECT p.timeIn,p.timeOut,
                                                CASE 
                                                WHEN (p.timeOut IS NULL) THEN 1
                                                WHEN (p.timeOut IS NOT NULL) THEN 2
                                                ELSE 0
                                                END AS DayNbPointing
                                                FROM App\Entity\Pointing p
                                                WHERE p.employee = :empId
                                                AND p.timeIn LIKE :dat 
                                                
        ")
            ->setParameters([
                'empId' => $this->getUser()->getId(),
                'dat'   => $date->format('Y-m-d') . '%'
            ])
            ->getResult();
        //dump($currentPointing);
        $dayNbPointing = count($currentPointing) > 0 ? intval($currentPointing[0]['DayNbPointing']) : 0;

        //Récupération du temps de travail déjà réalisé pour le mois en cours
        $currentMonthTotalTime = $manager->createQuery("SELECT SecToTime(SUM(TimeToSec(TIMEDIFF(COALESCE(p.timeOut,p.timeIn),p.timeIn)))) AS TotalWorkTime,
                                                        SUM(TimeToSec(TIMEDIFF(COALESCE(p.timeOut,p.timeIn),p.timeIn))) AS TotalWorkTimeInSecs
                                                        FROM App\Entity\Pointing p
                                                        WHERE p.employee = :empId
                                                        AND p.timeIn LIKE :dat 
                                                        AND p.timeOut LIKE :dat
                                                        AND p.statut = 'approved'
        ")
            ->setParameters([
                'empId' => $this->getUser()->getId(),
                'dat'   => $date->format('Y-m') . '%'
            ])
            ->getResult();

        //Récupération des temps de travail réalisé par jour pour le mois en cours
        $totalTimePerDay = $manager->createQuery("SELECT TIMEDIFF(COALESCE(p.timeOut,p.timeIn),p.timeIn) AS DailyWorkTime
                                                FROM App\Entity\Pointing p
                                                WHERE p.employee = :empId
                                                AND p.timeIn LIKE :dat 
                                                AND p.timeOut LIKE :dat
                                                AND p.statut = 'approved'
                                                ORDER BY p.timeIn ASC
        ")
            ->setParameters([
                'empId' => $this->getUser()->getId(),
                'dat'   => $date->format('Y-m') . '%'
            ])
            ->getResult();
        $wtperday = [];
        //Conversion des temps(HH:mm:ss) en heures uniquement => Ex : 09:30:00 --> 9.50
        foreach ($totalTimePerDay as $key => $value) {
            $wtperday[] = $this->timeToDecimal($value['DailyWorkTime']);
        }

        /*$point = $manager->createQuery("SELECT p
                                        FROM App\Entity\Pointing p
                                        WHERE p.employee = :empId
                                        AND p.timeIn LIKE :dat 
                                                         
                    ")
            ->setParameters([
                'empId' => $this->getUser()->getId(),
                'dat'   => $date->format('Y-m-d') . '%'
            ])
            ->getResult();*/
        //if (!empty($point))  dump($point[0]);
        //dump($currentMonthTotalTime);
        //dump($totalTimePerDay);
        //dump($wtperday);
        //dump($date->format('n'));
        //dump($this->workingDayNumber($date));
        //dump($this->timeToDecimal('09:30:30'));
        //dump($this->getUser()->getId());
        return $this->render('pointings/new.html.twig', [
            'totalWT'        => $currentMonthTotalTime[0]['TotalWorkTime'] ?? '00:00:00',
            'totalWTInSecs'  => intval($currentMonthTotalTime[0]['TotalWorkTimeInSecs']) ?? 0,
            'wtperday'       => $wtperday,
            'nbPointing'     => $dayNbPointing,
            'WDNb'           => $this->workingDayNumber($date), //Nombre de jours ouvrables
        ]);
    }

    /**
     * Permet de gérer les pointages
     * 
     * @Route("/pointing/action", name="pointing_action")
     * 
     * @Security( "is_granted('ROLE_USER') " )
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function pointingAction(Request $request, EntityManagerInterface $manager)
    {

        if ($this->getUser()->getEnterprise()->getIsActivated()) {
            $paramJSON = $this->getJSONRequest($request->getContent());
            $ray  = 0;
            $lat  = 0;
            $long = 0;

            if ((array_key_exists("type", $paramJSON) && !empty($paramJSON['type'])) && (array_key_exists("lat", $paramJSON) && !empty($paramJSON['lat'])) && (array_key_exists("long", $paramJSON) && !empty($paramJSON['long']))) {
                if (count($this->getUser()->getEnterprise()->getPointingLocations()) > 0) {
                    $ray  = $this->getUser()->getTeam()->getPointingLocation()->getRay();
                    $lat  = $this->getUser()->getTeam()->getPointingLocation()->getLatitude();
                    $long = $this->getUser()->getTeam()->getPointingLocation()->getLongitude();
                } else {
                    return $this->json([
                        'code'    => 403,
                        'message' => 'Zone de pointage non définie !',
                    ], 200);
                }
                $distance = $this->calcCrow($lat, $long, $paramJSON['lat'], $paramJSON['long']);
                if ($distance <= $ray) {
                    $date = new DateTime(date('Y-m-d H:i:s'));
                    if ($paramJSON['type'] === 'In') {
                        $pointing = new Pointing();
                        //$status = $this->getUser()->getRoles()[0] === 'ROLE_USER' ? 'on pending' : 'approved';
                        $status = 'on pending';
                        $pointing->setEmployee($this->getUser())
                            ->setStatut($status)
                            ->setTimeIn($date);

                        $manager->persist($pointing);
                    } else if ($paramJSON['type'] === 'Out') {
                        $point = $manager->createQuery("SELECT p
                                                        FROM App\Entity\Pointing p
                                                        WHERE p.employee = :empId
                                                        AND p.timeIn LIKE :dat 
                                                             
                        ")
                            ->setParameters([
                                'empId' => $this->getUser()->getId(),
                                'dat'   => $date->format('Y-m-d') . '%'
                            ])
                            ->getResult();
                        //dump($point);
                        if (!empty($point)) {
                            $pointing = $point[0];
                            $pointing->setTimeOut($date);

                            $manager->persist($pointing);
                        } else {
                            return $this->json([
                                'code'    => 403,
                                'message' => "Pointage d'entré non effectué !",
                            ], 200);
                        }
                        //$pointing->setTimeOut($date);
                    }
                    $manager->flush();

                    return $this->json([
                        'code'     => 200,
                        'distance' => $distance . ' km',
                        //'point'    => $point
                    ], 200);
                }
                return $this->json([
                    'code'    => 403,
                    'message' => 'Zone de pointage non autorisée !', 'distance' => $distance . ' km',
                ], 200);
            }

            return $this->json([
                'code' => 403,
                'message' => 'Empty Array or Not exists !',
            ], 403);
        } else {
            return $this->json([
                'code'    => 403,
                'message' => 'Abonnement Expiré ou en attente de paiement pour validation',
            ], 200);
        }
    }

    /**
     * Permet d'afficher les pointages en attente de validation
     *
     * @Route("/pointing/on-pending", name="pointings_onpending")
     * 
     * @Security( "is_granted('ROLE_LEADER')" )
     * 
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function onPendingPointings(EntityManagerInterface $manager)
    {
        $fuseau = $this->getUser()->getEnterprise()->getTimeZone() * 60 * 60;
        if ($this->getUser()->getRoles()[0] !== 'ROLE_LEADER') {
            //AND emp.attribut IN ('Leader','Subordinate')
            //AND emp.team IS NOT NULL
            $pointings = $manager->createQuery("SELECT p.id, p.employee AS Employee, SUBSTRING(p.timeIn,1,10) AS date_, AddTime(SUBSTRING(p.timeIn,12),SecToTime(:fus)) AS TimeIn, AddTime(SUBSTRING(p.timeOut,12),SecToTime(:fus)) AS TimeOut_, p.statut AS Status_,
                                            TIMEDIFF(COALESCE(p.timeOut,p.timeIn),p.timeIn) AS Duration
                                            FROM App\Entity\Pointing p
                                            WHERE p.employee IN (SELECT u.id FROM App\Entity\User u WHERE u.enterprise = :entId)
                                            AND p.statut = 'on pending' 
                                            ORDER BY p.timeIn ASC 
                                               
                                               
            ")
                ->setParameters(array(
                    'entId' => $this->getUser()->getEnterprise(),
                    'fus'   => $fuseau,
                ))
                ->getResult();
        } else {
            $pointings = $manager->createQuery("SELECT p.id, p, SUBSTRING(p.timeIn,1,10) AS date_, AddTime(SUBSTRING(p.timeIn,12),SecToTime(:fus)) AS TimeIn, AddTime(SUBSTRING(p.timeOut,12),SecToTime(:fus)) AS TimeOut_, p.statut AS Status_,
                                            TIMEDIFF(COALESCE(p.timeOut,p.timeIn),p.timeIn) AS Duration
                                            FROM App\Entity\Pointing p
                                            JOIN p.employee emp
                                            WHERE emp.team IN (SELECT t.id FROM App\Entity\Team t WHERE t.responsible = :user AND t.enterprise = :entId)
                                            AND p.statut = 'on pending' 
                                            ORDER BY p.timeIn ASC
            ")
                ->setParameters(array(
                    'entId' => $this->getUser()->getEnterprise(),
                    'user' => $this->getUser()->getId(),
                    'fus'   => $fuseau,
                ))
                ->getResult();
        }
        dump($pointings);
        return $this->render('pointings/onpendingPointing.html.twig', [
            'pointings' => $pointings,
        ]);
    }

    /**
     * Permet de modifier le statut des pointages
     * 
     * @Route("/pointing/change/status", name="pointings_change_status")
     * 
     * @Security( "is_granted('ROLE_LEADER')" )
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function pointingsChangeStatus(Request $request, EntityManagerInterface $manager)
    { //
        $paramJSON = $this->getJSONRequest($request->getContent());
        //$paramJSON = $request->request->get("pointingsApprovedIds");
        //dump($request->request);

        if (array_key_exists("pointingsApprovedIds", $paramJSON) && !empty($paramJSON['pointingsApprovedIds'])) {
            $flag = 0;

            foreach ($paramJSON['pointingsApprovedIds'] as $p) {
                //dump($p);

                //foreach ($p as $key => $json) {
                //$pointing_ = json_decode($p, true);

                //dump($pointing_['timeOut']);
                $pointing = $manager->getRepository('App:Pointing')->findOneBy(['id' => intval($p['id'])]);

                //Si le pointage existe et appartient à un employé de la même entreprise
                //if ($pointing && ($pointing->getUser()->getEnterprise() === $this->getUser()->getEnterprise())) {
                $timeIn = new DateTime($p['timeIn']);
                $timeOut = new DateTime($p['timeOut']);

                $pointing->setStatut('approved')
                    ->setTimeOut($timeOut ?? $pointing->getTimeOut())
                    ->setTimeIn($timeIn ?? $pointing->getTimeIn());

                $manager->persist($pointing);
                $flag++;
                //}

                //}
            }

            if ($flag) {
                $manager->flush();

                return $this->json([
                    'code'    => 200,
                    'message' => 'Approbation de pointage réussie !',
                ], 200);
            }

            return $this->json([
                'code'    => 403,
                'message' => 'Aucune sauvegarde de changement !',
            ], 200);
        }

        return $this->json([
            'code' => 403,
            'message' => 'Empty Array or Not exists !',
        ], 403);
    }

    /**
     * Permet l'enregistrement Manuel d'un Pointage
     * 
     * @Route("/pointing/record", name="record_pointing")
     * 
     * @Security( "is_granted('ROLE_LEADER') and user.getEnterprise().getIsActivated() == true" )
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function recordPointing(Request $request, EntityManagerInterface $manager)
    {
        $pointing = new Pointing();

        $team = null;
        if ($this->getUser()->getRoles()[0] === 'ROLE_LEADER') $team = $this->getUser()->getTeam();
        //Permet d'obtenir un constructeur de formulaire
        // Externaliser la création du formulaire avec la cmd php bin/console make:form
        //  instancier un form externe
        $form = $this->createForm(PointingType::class, $pointing, [
            'entId'  => $this->getUser()->getEnterprise()->getId(),
            'teamId' => $team !== null ? $team->getId() : 0,
        ]);
        $form->handleRequest($request);
        //dump($site);
        if ($form->isSubmitted() && $form->isValid()) {
            //dump($pointing);
            if ($this->getUser()->getEnterprise()->getTimeZone() > 0) {
                $timeZone = $this->getUser()->getEnterprise()->getTimeZone();
                $operator = 'sub';
            } else {
                $timeZone = $this->getUser()->getEnterprise()->getTimeZone() * (-1);
                $operator = 'add';
            }

            $hours = intval($timeZone);
            $mins  = intval(($timeZone - $hours) * 60);
            if (!$hours) $hours = '00';
            if (!$mins) $mins = '00';
            $dateIn = new DateTime($pointing->getTimeIn()->format('Y-m-d H:i:s'));
            $dateOut = new DateTime($pointing->getTimeOut()->format('Y-m-d H:i:s'));

            $point = $manager->createQuery("SELECT p
                                                    FROM App\Entity\Pointing p
                                                    WHERE p.employee = :empId
                                                    AND p.timeIn LIKE :dat 
                                                         
                    ")
                ->setParameters([
                    'empId' => $pointing->getEmployee()->getId(),
                    'dat'   => $dateIn->format('Y-m-d') . '%'
                ])
                ->getResult();
            //dd($point);
            if (!empty($point)) {
                $pointing = $point[0];
            }
            //dump($pointing);

            if ($operator === 'add') {
                $dateIn->add(new DateInterval("PT{$hours}H{$mins}M"));
                $dateOut->add(new DateInterval("PT{$hours}H{$mins}M"));
            } else {
                $dateIn->sub(new DateInterval("PT{$hours}H{$mins}M"));
                $dateOut->sub(new DateInterval("PT{$hours}H{$mins}M"));
            }

            $pointing->setStatut('approved')
                ->setTimeIn($dateIn)
                ->setTimeOut($dateOut);

            //dd($pointing);
            $manager->persist($pointing);
            $manager->flush();

            $this->addFlash(
                'success',
                "Le <strong> Pointage </strong> a été enregistré avec succès !"
            );


            return $this->redirectToRoute('employees_attendance_record');
        }


        return $this->render(
            'pointings/recordPointing.html.twig',
            [
                'form' => $form->createView(),
                'timeZone' => $this->getUser()->getEnterprise()->getTimeZone(),
            ]
        );
    }

    /**
     * Permet de supprimer un Utilisateur
     * 
     * @Route("/pointings/delete", name="delete_pointings")
     * 
     * @Security( "(is_granted('ROLE_LEADER')  )" )
     * 
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function delete(Request $request, EntityManagerInterface $manager)
    {
        $paramJSON = $this->getJSONRequest($request->getContent());

        if (array_key_exists("tabRecords", $paramJSON) && !empty($paramJSON['tabRecords'])) {
            $flag = 0;
            foreach ($paramJSON['tabRecords'] as $key => $id) {

                $pointing = $manager->getRepository('App:Pointing')->findOneBy(['id' => intval($id)]);

                //Si le pointage existe et appartient à un employé de la même entreprise
                if ($pointing && ($pointing->getEmployee()->getEnterprise() === $this->getUser()->getEnterprise())) {

                    $manager->remove($pointing);
                    $flag++;
                }
            }

            if ($flag) {
                $manager->flush();

                return $this->json([
                    'code'    => 200,
                    'message' => 'Suppression réussie !',
                ], 200);
            }

            return $this->json([
                'code'    => 403,
                'message' => 'Aucune suppression effectuée !',
            ], 200);
        }

        return $this->json([
            'code' => 403,
            'message' => 'Empty Array or Not exists !',
        ], 403);
    }
}
