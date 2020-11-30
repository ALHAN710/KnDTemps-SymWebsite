<?php

namespace App\Controller;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\ApplicationController;
use App\Entity\Pointing;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class PointingsController extends ApplicationController
{
    /**
     * @Route("/pointing/home", name="pointings")
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
     * @Security( "is_granted('ROLE_USER')" )
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function pointingAction(Request $request, EntityManagerInterface $manager)
    {
        $paramJSON = $this->getJSONRequest($request->getContent());

        if ((array_key_exists("type", $paramJSON) && !empty($paramJSON['type'])) && (array_key_exists("lat", $paramJSON) && !empty($paramJSON['lat'])) && (array_key_exists("long", $paramJSON) && !empty($paramJSON['long']))) {
            $ray  = $this->getUser()->getPointingLocation()->getRay();
            $lat  = $this->getUser()->getPointingLocation()->getLatitude();
            $long = $this->getUser()->getPointingLocation()->getLongitude();
            $distance = $this->calcCrow($lat, $long, $paramJSON['lat'], $paramJSON['long']);
            if ($distance <= $ray) {
                $date = new DateTime(date('Y-m-d H:i:s'));
                if ($paramJSON['type'] === 'In') {
                    $pointing = new Pointing();
                    $status = $this->getUser()->getRoles()[0] === 'ROLE_USER' ? 'on pending' : 'approved';
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
    }

    /**
     * Permet de modifier le statut des pointages
     * 
     * @Route("/pointing/change/status", name="pointings_change_status")
     * 
     * 
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function pointingsChangeStatus(Request $request, EntityManagerInterface $manager)
    { //@Security( "is_granted('ROLE_USER')" )
        $paramJSON = $this->getJSONRequest($request->getContent());
        //$paramJSON = $request->request->get("pointingsApprovedIds");
        dump($request->request);

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
                    'message' => 'Sauvegarde de changement réussi !',
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
}
