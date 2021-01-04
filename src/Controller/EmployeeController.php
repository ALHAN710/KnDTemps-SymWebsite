<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\ApplicationController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EmployeeController extends ApplicationController
{
    /**
     * @Route("/employees/list", name="employees_list")
     * 
     * @Security( "is_granted('ROLE_LEADER') or is_granted('ROLE_RH_MANAGER') or ( is_granted('ROLE_ADMIN') )" )
     * 
     */
    public function indexEmployees(EntityManagerInterface $manager): Response
    {
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

        return $this->render('employee/index_employees.html.twig', [
            'employees'       => $employees,
            'team'            => $team,
        ]);
    }


    /**
     * @Route("/employees/home/work-time", name="employees_home_work-time")
     * 
     * @Security( "is_granted('ROLE_LEADER') or is_granted('ROLE_RH_MANAGER') or ( is_granted('ROLE_ADMIN') )" )
     * 
     */
    public function indexEmployeesWorkTime(EntityManagerInterface $manager): Response
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

        return $this->render('employee/indexWorkTime.html.twig', [
            'employees'       => $employees,
            'team'            => $team,
        ]);
    }

    /**
     * Permet de mettre à jour le tableau de temps de travail des employés
     * 
     * @Route("update/employees/work-time", name="update_employees_dt_wk")
     *
     * @Security( "is_granted('ROLE_LEADER') or is_granted('ROLE_RH_MANAGER') or ( is_granted('ROLE_ADMIN') )" )
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
     * @Security( "is_granted('ROLE_LEADER') or is_granted('ROLE_RH_MANAGER') or ( is_granted('ROLE_ADMIN') )" )
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function employeeStats(Request $request, EntityManagerInterface $manager)
    {
        //$paramJSON = $this->getJSONRequest($request->getContent());
        $paramJSON = $request->request->get("params");
        //dump($paramJSON);
        //AND p.timeOut LIKE :dat

        if (!empty($paramJSON['date'])) {
            if ((array_key_exists("date", $paramJSON) && !empty($paramJSON['date'])) && (array_key_exists("emp", $paramJSON) && !empty($paramJSON['emp']))) {
                $emp = $manager->getRepository('App:User')->findOneBy(['id' => $paramJSON['emp']]);
                $fuseau = $this->getUser()->getEnterprise()->getTimeZone() * 60 * 60;
                if ($emp) {
                    $employeePointings = $manager->createQuery("SELECT p.id, SUBSTRING(p.timeIn,1,10) AS date_, AddTime(SUBSTRING(p.timeIn,12),SecToTime(:fus)) AS TimeIn, AddTime(SUBSTRING(p.timeOut,12),SecToTime(:fus)) AS TimeOut_, p.statut AS Status_,
                                                        TIMEDIFF(COALESCE(p.timeOut,p.timeIn),p.timeIn) AS Duration
                                                        FROM App\Entity\Pointing p
                                                        WHERE p.employee = :empId
                                                        AND p.timeIn LIKE :dat 
                                                        ORDER BY date_ DESC
                                                   
                    ")
                        ->setParameters([
                            'empId' => $paramJSON['emp'],
                            'dat'   => $paramJSON['date'] . '%',
                            'fus'   => $fuseau,
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
        }

        /*return $this->json([
            'code' => 403,
            'message' => 'Empty Array or Not exists !',
        ], 403);*/
        // throw $this->createNotFoundException('The product does not exist');
        // throw $this->createAccessDeniedException('Empty Array or Not exists !');
        throw new HttpException(500, 'Empty Array or Not exists !');
    }

    /**
     * Permet d'afficher la page de la fiche de présence des employés
     * 
     * @Route("/employees/attendance/record", name="employees_attendance_record")
     *
     * @Security( "is_granted('ROLE_LEADER') or is_granted('ROLE_RH_MANAGER') or ( is_granted('ROLE_ADMIN') )" )
     *
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function employeesAttendanceRecord(EntityManagerInterface $manager)
    {
        $team = null;
        $employeesAttendance  = [];
        $employees = null;
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
        $nowDate = new DateTime('now');
        $fuseau = $this->getUser()->getEnterprise()->getTimeZone() * 60 * 60;
        foreach ($employees as $employee) {
            //Récupération du temps de travail déjà réalisé pour le mois en cours
            //AND p.statut = 'approved'
            $employeeAttendance_ = $manager->createQuery("SELECT AddTime(SUBSTRING(p.timeIn,12),SecToTime(:fus)) AS TimeIn, AddTime(SUBSTRING(p.timeOut,12),SecToTime(:fus)) AS TimeOut_, p.statut AS Statut,
                                                        CASE 
                                                        WHEN (p.timeIn IS NULL) THEN 'absent'
                                                        WHEN (p.timeIn IS NOT NULL) THEN 'présent'
                                                        ELSE 'absent'
                                                        END AS PresenceStatus
                                                        FROM App\Entity\Pointing p
                                                        WHERE p.employee = :empId
                                                        AND (p.timeIn LIKE :dat OR p.timeOut LIKE :dat)
                                                        
                ")
                ->setParameters([
                    'empId' => $employee->getId(),
                    'dat'   => $nowDate->format('Y-m-d') . '%',
                    'fus'   => $fuseau,
                ])
                ->getResult();

            if (!empty($employeeAttendance_)) {
                $employeesAttendance[$employee->getId()] = [
                    'TimeIn'         => $employeeAttendance_[0]['TimeIn'] ?? '-',
                    'TimeOut'        => $employeeAttendance_[0]['TimeOut_'] ?? '-',
                    'PresenceStatus' => $employeeAttendance_[0]['PresenceStatus'],
                    'Statut'         => $employeeAttendance_[0]['Statut'],
                ];
            } else {
                $employeesAttendance[$employee->getId()] = [
                    'TimeIn'         => '-',
                    'TimeOut'        => '-',
                    'PresenceStatus' => 'absent',
                    'Statut'         => 'approved',
                ];
            }
        }
        //dump($employeesAttendance);
        //dump($this->getUser()->getEnterprise()->getTimeZone());
        return $this->render('employee/attendanceRecord.html.twig', [
            'employees'         => $employees,
            'employeesAttendance' => (array)$employeesAttendance,
            'team'              => $team,
        ]);
    }

    /**
     * Permet de mettre à jour le tableau de présence des employés
     * 
     * @Route("update/employees/attendance/record", name="update_employees_dt_attendance")
     *
     * @Security( "is_granted('ROLE_LEADER') or is_granted('ROLE_RH_MANAGER') or ( is_granted('ROLE_ADMIN') )" )
     * 
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function updateEmployeesAttendanceRecord(Request $request, EntityManagerInterface $manager)
    {
        $employeesAttendance  = [];

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
            $fuseau = $this->getUser()->getEnterprise()->getTimeZone() * 60 * 60;
            foreach ($employees as $employee) {
                //Récupération du temps de travail déjà réalisé pour le mois en cours
                //AND p.statut = 'approved'
                $employeeAttendance_ = $manager->createQuery("SELECT AddTime(SUBSTRING(p.timeIn,12),SecToTime(:fus)) AS TimeIn, AddTime(SUBSTRING(p.timeOut,12),SecToTime(:fus)) AS TimeOut_, p.statut AS Statut,
                                                        CASE 
                                                        WHEN (p.timeIn IS NULL) THEN 'absent'
                                                        WHEN (p.timeIn IS NOT NULL) THEN 'présent'
                                                        ELSE 'absent'
                                                        END AS PresenceStatus
                                                        FROM App\Entity\Pointing p
                                                        WHERE p.employee = :empId
                                                        AND (p.timeIn LIKE :dat OR p.timeOut LIKE :dat)
                                                        
                ")
                    ->setParameters([
                        'empId' => $employee->getId(),
                        'dat'   => $paramJSON['date'] . '%',
                        'fus'   => $fuseau,
                    ])
                    ->getResult();

                if (!empty($employeeAttendance_)) {
                    $employeesAttendance[] = [
                        'Id'             => $employee->getId(),
                        'TimeIn'         => $employeeAttendance_[0]['TimeIn'] ?? '-',
                        'TimeOut'        => $employeeAttendance_[0]['TimeOut_'] ?? '-',
                        'PresenceStatus' => $employeeAttendance_[0]['PresenceStatus'],
                        'Statut'         => $employeeAttendance_[0]['Statut'],
                    ];
                } else {
                    $employeesAttendance[] = [
                        'Id'             => $employee->getId(),
                        'TimeIn'         => '-',
                        'TimeOut'        => '-',
                        'PresenceStatus' => 'absent',
                        'Statut'         => 'approved',
                    ];
                }
            }
            //dump($employeesAttendance);
            return $this->json([
                'code'              => 200,
                'employeesAttendance' => (array)$employeesAttendance,
                'message'           => 'Success !',
            ], 200);
        }

        return $this->json([
            'code' => 403,
            'message' => 'Empty Array or Not exists !',
        ], 403);
    }

    /**
     * Permet d'avoir la fiche de présence d'un employé pour un mois donné
     * 
     * @Route("/employee/{id<\d+>}/month/attendance/sheet", name="employee_month_attendance_sheet")
     * 
     * @Security( "(emp === user) or ( is_granted('ROLE_LEADER') and user.getTeam().getUsers().contains(emp) ) or ( (is_granted('ROLE_RH_MANAGER') or is_granted('ROLE_ADMIN')) and (emp.getEnterprise() === user.getEnterprise()) )" )
     * 
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function employeeAttendanceRecord(User $emp, Request $request, EntityManagerInterface $manager)
    {
        //$paramJSON = $this->getJSONRequest($request->getContent());
        $paramJSON = $request->request->get("params");
        //dump($paramJSON);
        //AND p.timeOut LIKE :dat

        if ((array_key_exists("date", $paramJSON) && !empty($paramJSON['date']))) {
            //$emp = $manager->getRepository('App:User')->findOneBy(['id' => $paramJSON['emp']]);

            //if ($emp) {
            $fuseau = $this->getUser()->getEnterprise()->getTimeZone() * 60 * 60;
            $employeeAttendance = $manager->createQuery("SELECT SUBSTRING(p.timeIn,1,10) AS date_, AddTime(SUBSTRING(p.timeIn,12),SecToTime(:fus)) AS TimeIn, AddTime(SUBSTRING(p.timeOut,12),SecToTime(:fus)) AS TimeOut_, p.statut AS Statut,
                                                        CASE 
                                                        WHEN (p.timeIn IS NULL) THEN 'absent'
                                                        WHEN (p.timeIn IS NOT NULL) THEN 'présent'
                                                        ELSE 'absent'
                                                        END AS PresenceStatus
                                                        FROM App\Entity\Pointing p
                                                        WHERE p.employee = :empId
                                                        AND (p.timeIn LIKE :dat OR p.timeOut LIKE :dat)
                                                        
                ")
                ->setParameters([
                    'empId' => $emp->getId(),
                    'dat'   => $paramJSON['date'] . '%',
                    'fus'   => $fuseau,
                ])
                ->getResult();

            //dd($employeeAttendance);
            $date = new DateTime($paramJSON['date'] . '-01');
            return $this->render('employee/attendanceSheet.html.twig', [
                'emp'                => $emp,
                'employeeAttendance' => $employeeAttendance,
                'month'              => $date->format('M Y'),
                'date'               => $date,
            ]);
            //}
        }

        return $this->json([
            'code' => 403,
            'message' => 'Empty Array or Not exists !',
        ], 403);
    }

    /**
     * Permet de mettre à jour le tableau de présence de l'employé passé en paramètre
     * 
     * @Route("update/employee/{id<\d+>}/attendance/record", name="update_employee_dt_presence")
     *
     * @Security( "(emp === user) or (is_granted('ROLE_LEADER') and user.getTeam().getUsers().contains(emp)) or ( (is_granted('ROLE_RH_MANAGER') or is_granted('ROLE_ADMIN')) and (emp.getEnterprise() === user.getEnterprise()) )" )
     * 
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return void
     */
    public function updateEmployeeAttendanceRecord(User $emp, Request $request, EntityManagerInterface $manager)
    {
        $employeeAttendance  = [];

        $paramJSON = $this->getJSONRequest($request->getContent());
        //AND emp.attribut IN ('Leader','Subordinate')
        //AND emp.team IS NOT NULL
        if ((array_key_exists("date", $paramJSON) && !empty($paramJSON['date']))) {
            //$emp = $manager->getRepository('App:User')->findOneBy(['id' => $paramJSON['emp']]);

            //if ($emp) {
            $fuseau = $this->getUser()->getEnterprise()->getTimeZone() * 60 * 60;
            //Récupération des présence au travail pour un mois donné
            //AND p.statut = 'approved'
            $employeeAttendance = $manager->createQuery("SELECT SUBSTRING(p.timeIn,1,10) AS date_, AddTime(SUBSTRING(p.timeIn,12),SecToTime(:fus)) AS TimeIn, AddTime(SUBSTRING(p.timeOut,12),SecToTime(:fus)) AS TimeOut_, p.statut AS Statut,
                                                        CASE 
                                                        WHEN (p.timeIn IS NULL) THEN 'absent'
                                                        WHEN (p.timeIn IS NOT NULL) THEN 'présent'
                                                        ELSE 'absent'
                                                        END AS PresenceStatus
                                                        FROM App\Entity\Pointing p
                                                        WHERE p.employee = :empId
                                                        AND (p.timeIn LIKE :dat OR p.timeOut LIKE :dat)
                                                        
                ")
                ->setParameters([
                    'empId' => $emp->getId(),
                    'dat'   => $paramJSON['date'] . '%',
                    'fus'   => $fuseau,
                ])
                ->getResult();
            //}

            //dump($employeesAttendance);
            foreach ($employeeAttendance as $key => $value) {
                $date = new DateTime($value['date_'] . '00:00:00');
                $employeeAttendance[$key]['date_'] = $date->format('D, d M Y');
            }
            return $this->json([
                'code'              => 200,
                'employeeAttendance' => (array)$employeeAttendance,
                'message'           => 'Success !',
            ], 200);
        }

        return $this->json([
            'code' => 403,
            'message' => 'Empty Array or Not exists !',
        ], 403);
    }
}
