<?php

namespace App\DataFixtures;

use Faker;
use DateTime;
use App\Entity\Role;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\Office;
use App\Entity\Enterprise;
use App\Entity\Pointing;
use App\Entity\PointingLocation;
use App\Entity\Subscription;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    //Constructeur pour utiliser la fonction d'encodage de mot passe
    //encodePassword($entity, $password)
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');
        $faker->seed(1337);

        $nb = 0;
        $genders = ['male', 'female'];

        //Génération des jours ouvrables du mois n = [1..12]
        $n = '10';
        $workdays = array();
        $type = CAL_GREGORIAN;
        $month = date($n); // Month ID, 1 through to 12.
        $year = date('Y'); // Year in 4 digit 2009 format.
        $day_count = cal_days_in_month($type, $month, $year); // Get the amount of days

        //loop through all days
        for ($i = 1; $i <= $day_count; $i++) {

            $date = $year . '-' . $month . '-' . $i; //format date
            $get_name = date('l', strtotime($date)); //get week day
            $day_name = substr($get_name, 0, 3); // Trim day name to 3 chars

            //if not a weekend add day to array
            if ($day_name != 'Sun' && $day_name != 'Sat') {
                //$workdays[] = $i;
                $workdays[] = $date;
            }
        }
        //dump($workdays);

        //Génération des temps de pointage
        $timeIn  = ['08:00:00', '08:15:00', '08:30:00', '09:00:00', '09:15:00', '09:30:00', '10:00:00'];
        $timeOut = ['16:00:00', '16:15:00', '16:30:00', '17:00:00', '17:15:00', '17:30:00', '18:00:00', '18:30:00'];

        //Création des rôles
        $leaderRole = new Role();
        $leaderRole->setTitle('ROLE_LEADER');
        $manager->persist($leaderRole);
        $adminRole = new Role();
        $adminRole->setTitle('ROLE_ADMIN');
        $manager->persist($adminRole);
        $superAdminRole = new Role();
        $superAdminRole->setTitle('ROLE_SUPER_ADMIN');
        $manager->persist($superAdminRole);

        //Création des abonnements
        $abonnementName = ['KnD Time Classic', 'KnD Time Basic', 'KnD Time Premium', 'KnD Time Gold'];
        $employeeNb = [10, 35, 100, 7102020];
        //$refNb  = [0, 0, 50, 50];
        $tarifs = [
            '{"1": 4500,"6": 24000,"12": 45000}',
            '{"1": 12000,"6": 59400,"12": 110000}', '{"1": 25000,"6": 74400,"12": 140000}',
            '{"1": 40000,"6": 96000,"12": 190000}',
        ];
        $subs = [];
        foreach ($tarifs as $key => $value) {
            $sub = new Subscription();
            $sub->setName($abonnementName[$key])
                ->setTarifs(json_decode($value, true))
                ->setEmployeeNumber($employeeNb[$key]);
            //->setProductRefNumber($refNb[$key]);

            $subs[] = $sub;
            $manager->persist($sub);
        }

        //Création d'entreprise
        $enterprise = new Enterprise();
        $enterprise->setSocialReason('LEELOU BABY FOOD SAS')
            ->setNiu('M 042014440616 M')
            ->setRccm('RC/DCN/2020/13/770')
            ->setAddress('Bépanda Camtel, BP : 2702 Douala')
            ->setPhoneNumber('+237 694342007')
            //->setTva(19.25)
            ->setEmail('contact@leeloubabyfood.com')
            ->setCountry('Cameroon')
            ->setTown('Douala')
            ->setSubscription($subs[2])
            ->setSubscriptionDuration(6);
        $manager->persist($enterprise);

        //Création des superAdmin
        //$date = new DateTime(date('Y-m-d H:i:s'));
        $adminUser = new User();
        $date      = new DateTime(date('Y-m-d H:i:s'));
        $adminUser->setEmail('alhadoumpascal@gmail.com')
            ->setFirstName('Pascal')
            ->setLastName('ALHADOUM')
            ->setVerified(true)
            ->setCreatedAt($date)
            ->setGrade('M.')
            ->setSex($genders[0])
            ->setHash($this->encoder->encodePassword($adminUser, 'password'))
            ->addUserRole($superAdminRole)
            ->setPhoneNumber('690442311');

        $manager->persist($adminUser);

        $adminUser = new User();
        $date      = new DateTime(date('Y-m-d H:i:s'));
        $adminUser->setEmail('cabrelmbakam@gmail.com')
            ->setFirstName('Cabrel')
            ->setLastName('MBAKAM')
            ->setVerified(true)
            ->setGrade('M.')
            ->setSex($genders[0])
            ->setCreatedAt($date)
            ->setHash($this->encoder->encodePassword($adminUser, 'password'))
            ->addUserRole($superAdminRole)
            ->setPhoneNumber('690304593');

        $manager->persist($adminUser);

        //Création de l'administrateur Entreprise
        $adminUser = new User();
        $date      = new DateTime(date('Y-m-d H:i:s'));
        $adminUser->setEmail('naomidinamona@gmail.com')
            ->setFirstName('Naomi')
            ->setLastName('DINAMONA')
            ->setVerified(true)
            ->setCreatedAt($date)
            ->setGrade('Mme')
            ->setSex($genders[1])
            ->setHash($this->encoder->encodePassword($adminUser, 'password'))
            ->addUserRole($adminRole)
            ->setEnterprise($enterprise)
            ->setPhoneNumber('654289625');

        $manager->persist($adminUser);

        //Statut de pointage
        $statuts = ['on pending', 'approved', 'disapproved'];

        $lat = 3.1524;
        $long = 10.2536;
        $pointingLocation = new PointingLocation();
        $pointingLocation->setLatitude($lat)
            ->setLongitude($long)
            ->setRay(5);

        $manager->persist($pointingLocation);

        //Création des fonctions ou poste
        $officeName = ['Livreur', 'Cuisinière', 'Commerciale', 'Comptable', 'QHSE'];
        $employeNb  = [2, 3, 2, 2, 1];
        $salary     = [50000, 70000, 80000, 85000, 80000];
        $offices    = [];
        $attribut   = ['Subordinate', 'Leader'];
        $employees  = [];
        $leaders    = [];

        $teamName = ['Livraison', 'Production', 'Commerciale', 'Comptable'];
        $teams    = [];

        $oneTime = -1;
        foreach ($officeName as $key => $value) {
            $office = new Office();
            $office->setName($value)
                ->setHourlySalary($salary[$key] / 20.0)
                ->setMonthlySalary($salary[$key])
                ->setEnterprise($enterprise);
            //->setProductRefNumber($refNb[$key]);
            $offices[] = $office;
            $manager->persist($office);

            for ($i = 1; $i <= $employeNb[$key]; $i++) {
                $employee = new User();
                $hash     = $this->encoder->encodePassword($employee, 'password');
                $date     = new DateTime(date('Y-m-d H:i:s'));
                $gender   = $value === 'Livreur' ? $genders[0] : $genders[1];
                $isLeader = 0;

                if ($oneTime !== $key && $value !== 'QHSE') {
                    $oneTime  = $key;
                    $isLeader = 1;
                }

                $employee->setFirstName($faker->unique()->firstName($gender))
                    ->setLastName($faker->unique()->lastName)
                    ->setEmail($faker->unique()->companyEmail)
                    ->setCreatedAt($date)
                    ->setHash($hash)
                    ->setVerified(true)
                    ->setPhoneNumber($faker->unique()->phoneNumber)
                    ->setCountryCode('+237')
                    ->setOffice($office)
                    ->setEnterprise($enterprise)
                    ->setWtperday(8)
                    ->setCommission(0.0)
                    ->setBornAt($date)
                    ->setStatut('In Function')
                    ->setHiringAt($date)
                    ->setGrade($value === 'Livreur' ? 'M.' : 'Mme')
                    ->setSex($value === 'Livreur' ? $genders[0] : $genders[1])
                    ->setAttribut($attribut[$isLeader])
                    ->setPointingLocation($pointingLocation);

                $employees[] = $employee;

                //Création des équipes
                if ($isLeader === 1) {
                    $team = new Team();
                    $team->setName($teamName[$key])
                        ->setResponsible($employee)
                        ->setEnterprise($enterprise);

                    $teams[] = $team;
                    $manager->persist($team);

                    $employee->setTeam($team)
                        ->addUserRole($leaderRole);
                    if ($value !== 'QHSE') $team_ = $team;
                    //$leaders[] = $employee;
                }
                if ($value !== 'QHSE') {
                    $employee->setTeam($team_);
                }

                //Création des pointages
                foreach ($workdays as $key_ => $value_) {
                    $time_in  = $faker->randomElement($timeIn);
                    $time_out = $faker->randomElement($timeOut);
                    $dateIn   = new DateTime($value_ . ' ' . $time_in);
                    $dateOut  = new DateTime($value_ . ' ' . $time_out);

                    $pointing = new Pointing();
                    $pointing->setTimeIn($dateIn)
                        ->setTimeOut($dateOut)
                        //->setType('In')
                        ->setEmployee($employee)
                        ->setStatut('approved'); //$faker->randomElement($statuts)
                    $manager->persist($pointing);

                    /*$pointingOut = new Pointing();
                    $pointingOut->setTime($dateOut)
                        ->setType('Out')
                        ->setEmployee($employee)
                        ->setStatut($faker->randomElement($statuts));
                    $manager->persist($pointingOut);*/
                }

                $manager->persist($employee);
            }
        }

        $manager->flush();
    }
}
