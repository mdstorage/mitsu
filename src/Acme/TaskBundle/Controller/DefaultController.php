<?php

namespace Acme\TaskBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $marks = array('Huyndai', 'KIA', 'Honda', 'Suzuki', 'Subaru', 'Mazda', 'Mercedes', 'BMV', 'Mini');
        return $this->render('AcmeTaskBundle:Default:index.html.twig', array('marks' => $marks));
    }
}
