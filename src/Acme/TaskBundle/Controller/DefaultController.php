<?php

namespace Acme\TaskBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {setcookie('cookiehost', '');
        $marks = array('Hyundai', 'KIA', 'Honda', 'Suzuki', 'Subaru', 'Mazda', 'Mercedes', 'Smart', 'BMW', 'BMWMoto', 'Mini', 'RollsRoyce', 'Saab', 'Audi', 'Volkswagen',
            'Seat', 'Skoda', 'HondaEurope', 'Fiat', 'FiatProfessional', 'Lancia', 'AlfaRomeo', 'Abarth', 'Nissan', 'Infiniti', 'LandRover', 'ChevroletUsa', 'Cadillac', 'Pontiac',
            'Buick', 'Hummer', 'Saturn', 'GMC', 'Oldsmobile', 'Toyota', 'Lexus');
        sort($marks);
        reset($marks);
        return $this->render('AcmeTaskBundle:Default:index.html.twig', array('marks' => $marks));
    }
}
