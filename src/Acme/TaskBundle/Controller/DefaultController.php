<?php

namespace Acme\TaskBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $marks = array('Huyndai', 'KIA', 'Honda', 'Suzuki', 'Subaru', 'Mazda', 'Mercedes', 'Smart', 'BMW', 'Mini', 'RollsRoyce', 'Saab', 'Audi', 'Volkswagen',
            'Seat', 'Skoda', 'HondaEurope', 'Fiat', 'FiatProfessional', 'Lancia', 'AlfaRomeo', 'Abarth', 'Nissan', 'Infiniti', 'LandRover', 'ChevroletUsa', 'Cadillac', 'Pontiac');
        sort($marks);
        reset($marks);
        return $this->render('AcmeTaskBundle:Default:index.html.twig', array('marks' => $marks));
    }
}
