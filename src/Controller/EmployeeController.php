<?php

namespace App\Controller;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\ApplicationController;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EmployeeController extends ApplicationController
{
    /**
     * @Route("/employees/home", name="employees_home")
     */
    public function index(EntityManagerInterface $manager): Response
    {
        $date = new DateTime('2020-10-07');
        $team = null;
        if ($this->getUser()->getRoles()[0] !== 'ROLE_LEADER') {
            //AND emp.attribut IN ('Leader','Subordinate')
            //AND emp.team IS NOT NULL
            $employees = $manager->createQuery("SELECT emp
                                               FROM App\Entity\User emp
                                               WHERE emp.enterprise = :entId
                                               AND emp.statut = 'In Function'
                                               
                                               
            ")
                ->setParameters(array(
                    'entId' => $this->getUser()->getEnterprise(),
                ))
                ->getResult();
        } else {
            $employees = $manager->createQuery("SELECT emp
                                               FROM App\Entity\User emp
                                               WHERE emp.enterprise = :entId
                                               AND emp.statut = 'In Function'
                                               AND emp.team = :teamId
                                               
            ")
                ->setParameters(array(
                    'entId' => $this->getUser()->getEnterprise(),
                    'teamId' => $this->getUser()->getTeam()->getId(),
                ))
                ->getResult();
            $team = $this->getUser()->getTeam();
        }
        //dump($employees);
        //Récupération du temps de travail déjà réalisé pour le mois en cours
        /*$currentMonthTotalTime = $manager->createQuery("SELECT p.employee, SecToTime(SUM(TimeToSec(TIMEDIFF(COALESCE(p.timeOut,p.timeIn),p.timeIn)))) AS TotalWorkTime,
                                                        SUM(TimeToSec(TIMEDIFF(COALESCE(p.timeOut,p.timeIn),p.timeIn))) AS TotalWorkTimeInSecs
                                                        FROM App\Entity\Pointing p
                                                        WHERE p.employee IN (SELECT u.id FROM App\Entity\User u WHERE u.enterprise = :entId)
                                                        AND p.timeIn LIKE :dat 
                                                        AND p.timeOut LIKE :dat
                                                        AND p.statut = 'approved'
                                                        GROUP BY p.employee
        ")
            ->setParameters([
                'entId' => $this->getUser()->getEnterprise(),
                'dat'   => $date->format('Y-m') . '%'
            ])
            ->getResult();
        dump($currentMonthTotalTime);*/

        return $this->render('employee/index.html.twig', [
            'controller_name' => 'EmployeeController',
            'employees'       => $employees,
            'team'            => $team,
        ]);
    }

    /**
     * Permet de mettre à jour le tableau de temps de travail des employés
     * 
     * @Route("update/employees/work-time", name="update_employees_dt_wk")
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function updateEmployeesTable(Request $request, EntityManagerInterface $manager)
    {
        $employeesWorkTime  = [];

        $paramJSON = $this->getJSONRequest($request->getContent());
        //AND emp.attribut IN ('Leader','Subordinate')
        //AND emp.team IS NOT NULL
        if ((array_key_exists("date", $paramJSON) && !empty($paramJSON['date']))) {
            $employees = $manager->createQuery("SELECT emp
                                           FROM App\Entity\User emp
                                           WHERE emp.enterprise = :entId
                                           AND emp.statut = 'In Function'
                                           
                                           
            ")
                ->setParameters(array(
                    'entId' => $this->getUser()->getEnterprise(),
                ))
                ->getResult();

            foreach ($employees as $employee) {
                //Récupération du temps de travail déjà réalisé pour le mois en cours
                $employeeWorkTime_ = $manager->createQuery("SELECT SecToTime(SUM(TimeToSec(TIMEDIFF(COALESCE(p.timeOut,p.timeIn),p.timeIn)))) AS TotalWorkTime
                                                        FROM App\Entity\Pointing p
                                                        WHERE p.employee = :empId
                                                        AND p.timeIn LIKE :dat 
                                                        AND p.timeOut LIKE :dat
                                                        AND p.statut = 'approved'
                ")
                    ->setParameters([
                        'empId' => $employee->getId(),
                        'dat'   => $paramJSON['date'] . '%'
                    ])
                    ->getResult();
                $employeesWorkTime[] = [
                    'Id' => $employee->getId(),
                    'WT' => $employeeWorkTime_[0]['TotalWorkTime'] ?? '00:00:00',
                ];
            }
            //dump($employeesWorkTime);
            return $this->json([
                'code'              => 200,
                'employeesWorkTime' => (array)$employeesWorkTime,
                'message'           => 'Success !',
            ], 200);
        }

        return $this->json([
            'code' => 403,
            'message' => 'Empty Array or Not exists !',
        ], 403);
    }

    /**
     * Permet d'avoir les données de pointage d'un employé pour un mois donné
     * 
     * @Route("/employee/month/pointings", name="employee_pointings")
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function employeeStats(Request $request, EntityManagerInterface $manager)
    {
        //$paramJSON = $this->getJSONRequest($request->getContent());
        $paramJSON = $request->request->get("params");
        if ((array_key_exists("date", $paramJSON) && !empty($paramJSON['date'])) && (array_key_exists("emp", $paramJSON) && !empty($paramJSON['emp']))) {
            $emp = $manager->getRepository('App:User')->findOneBy(['id' => $paramJSON['emp']]);

            if ($emp) {
                $employeePointings = $manager->createQuery("SELECT p.id, SUBSTRING(p.timeIn,1,10) AS date_, SUBSTRING(p.timeIn,12) AS TimeIn, SUBSTRING(p.timeOut,12) AS TimeOut_, p.statut AS Status_,
                                                    TIMEDIFF(COALESCE(p.timeOut,p.timeIn),p.timeIn) AS Duration
                                                    FROM App\Entity\Pointing p
                                                    WHERE p.employee = :empId
                                                    AND p.timeIn LIKE :dat 
                                                    AND p.timeOut LIKE :dat
                                                    ORDER BY date_ DESC
                                               
                ")
                    ->setParameters([
                        'empId' => $paramJSON['emp'],
                        'dat'   => $paramJSON['date'] . '%'
                    ])
                    ->getResult();
                //dump($employeePointings);
                $date = new DateTime($paramJSON['date'] . '-01');
                return $this->render('employee/workTimeSheet.html.twig', [
                    'emp'               => $emp,
                    'employeePointings' => $employeePointings,
                    'month'             => $date->format('M Y'),
                ]);
            }
        }

        return $this->json([
            'code' => 403,
            'message' => 'Empty Array or Not exists !',
        ], 403);
    }
}
