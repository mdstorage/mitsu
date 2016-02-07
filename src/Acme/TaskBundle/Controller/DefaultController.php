<?php

namespace Acme\TaskBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $marks = array('Huyndai', 'KIA', 'Honda', 'Suzuki', 'Subaru', 'Mazda', 'Mercedes', 'BMW', 'Mini', 'RollsRoyce', 'Saab', 'Audi', 'Volkswagen',
            'Seat', 'Skoda', 'HondaEurope');
        return $this->render('AcmeTaskBundle:Default:index.html.twig', array('marks' => $marks));
    }
}
