<?php

namespace App\Controller;

use DateTimeInterface;
use Symfony\Component\Mime\Email;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ApplicationController extends AbstractController
{
    public function getJSONRequest($content): array
    {
        //$content = $request->getContent();
        //$content = $this->getContentAsArray($request);
        //dump($content);
        if (empty($content)) {
            throw new BadRequestHttpException("Content is empty");
            /*return $this->json([
                'code' => 400,
                'error' => "Content is empty"
            ], 400);*/
        }

        //$var = $this->json_validate($content);

        $paramJSON = json_decode($content, true); //json_decode("{\"date\":\"2020-03-20\",\"sa\":1.5}", true); 

        // switch and check possible JSON errors
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $error = ''; // JSON is valid // No error has occurred
                break;
            case JSON_ERROR_DEPTH:
                $error = 'The maximum stack depth has been exceeded.';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Invalid or malformed JSON.';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Control character error, possibly incorrectly encoded.';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON.';
                break;
                // PHP >= 5.3.3
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
                break;
                // PHP >= 5.5.0
            case JSON_ERROR_RECURSION:
                $error = 'One or more recursive references in the value to be encoded.';
                break;
                // PHP >= 5.5.0
            case JSON_ERROR_INF_OR_NAN:
                $error = 'One or more NAN or INF values in the value to be encoded.';
                break;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $error = 'A value of a type that cannot be encoded was given.';
                break;
            default:
                $error = 'Unknown JSON error occured.';
                break;
        }
        if (!empty($error)) {
            throw new BadRequestHttpException($error);
            /*return $this->json([
                'code' => 400,
                'error' => $error

            ], 400);*/
        }

        return  $paramJSON;
    }

    /**
     * Fonction de conversion de TIME (HH:mm:ss) en heures uniquement ==> Ex : 09:30:00 ----> 9.5h
     *
     * @param [type] $time
     * @return void
     */
    public function timeToDecimal($time)
    {
        $hm = explode(":", $time);
        $hour = $hm[0] + ($hm[1] / 60.0) + ($hm[2] / 3600.0);
        return number_format($hour, 2, '.', ' ');
    }

    /**
     * Permet de déterminer le nombre de jour ouvrable
     *
     * @param \DateTimeInterface $date
     * @return void
     */
    public function workingDayNumber(\DateTimeInterface $date)
    {
        $workdays = array();
        $type = CAL_GREGORIAN;
        $month = $date->format('n'); // Month ID, 1 through to 12.
        $year = $date->format('Y'); // Year in 4 digit 2009 format.
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
        return count($workdays);
    }

    function calcCrow($lat1, $lon1, $lat2, $lon2)
    {
        $r = 6371; // km
        $dLat = $this->toRad($lat2 - $lat1);
        $dLon = $this->toRad($lon2 - $lon1);
        $lat1 = $this->toRad($lat1);
        $lat2 = $this->toRad($lat2);

        $a = sin($dLat / 2) * sin($dLat / 2) + sin($dLon / 2) * sin($dLon / 2) * cos($lat1) * cos($lat2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $d = $r * $c;
        return $d;
    }

    // Converts numeric degrees to radians
    function toRad($value)
    {
        return $value * pi() / 180;
    }

    /**
     * Permet d'envoyer les emails
     *
     * @param [MailerInterface] $mailer
     * @param [string] $addressMail
     * @param  $object
     * @param [string] $mess
     * @return void
     */
    public function sendEmail($mailer, $addressMail, $object, $mess)
    {
        $email = (new Email())
            ->from('donotreply@portal-myenergyclever.com')
            ->to($addressMail)
            //->addTo('cabrelmbakam@gmail.com')
            //->cc('cabrelmbakam@gmail.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject($object)
            ->text($mess);
        //->html('<p>See Twig integration for better HTML integration!</p>');

        $mailer->send($email);

        // ...
    }
}
