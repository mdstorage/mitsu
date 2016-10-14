<?php

namespace Acme\TaskBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Catalog\CommonBundle\Components\Constants;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Catalog\CommonBundle\Components\Factory;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $marks = array('Hyundai', 'KIA', 'Honda', 'Suzuki', 'Subaru', 'Mazda', 'Mercedes', 'Smart', 'BMW', 'BMWMoto', 'Mini', 'RollsRoyce', 'Saab', 'Audi', 'Volkswagen',
            'Seat', 'Skoda', 'HondaEurope', 'Fiat', 'FiatProfessional', 'Lancia', 'AlfaRomeo', 'Abarth', 'Nissan', 'Infiniti', 'LandRover', 'ChevroletUsa', 'Cadillac', 'Pontiac',
            'Buick', 'Hummer', 'Saturn', 'GMC', 'Oldsmobile', 'Toyota', 'Lexus');
        sort($marks);
        reset($marks);
        return $this->render('AcmeTaskBundle:Default:index.html.twig', array('marks' => $marks));
    }

    public function resultAction(Request $request)
    {

        if ($request->isXmlHttpRequest()) {

            $vin = $request->get('vin');

            $marks = array('Pontiac','Abarth', 'AlfaRomeo', 'Fiat', 'FiatProfessional', 'Lancia', 'Bmw', 'Hyundai', 'Kia', 'RollsRoyce', 'Audi', 'Seat', 'Skoda', 'Volkswagen',
                            'ChevroletUsa', 'Cadillac', 'Pontiac', 'Buick', 'Hummer', 'Saturn', 'GMC', 'Oldsmobile', 'Mercedes', 'Smart', 'Honda', 'Hondaeurope', 'Mercedes',
                            'Nissan', 'Infiniti', 'Toyota', 'Lexus', 'Subaru', 'Suzuki', 'Mazda');
            $marksVAG = array('Audi', 'Seat', 'Skoda', 'Volkswagen');

            $result = array();

            foreach ($marks as $mark)
            {
                if (!in_array($mark, $marksVAG))
                {
                    $result[$mark] = $this->get(strtolower($mark).'.vin.model')->getVinFinderResult($vin);

                    if ($result[$mark] == null)
                    {
                        unset ($result[$mark]);
                    }

                }
                else {

                    $result[$mark] = $this->get(strtolower($mark).'.vin.model')->getVinRegions($vin);

                    if ($result[$mark] == null)
                    {
                        unset ($result[$mark]);
                    }
                }
            }


            if (!$result) {
                return $this->render('CatalogBmwBundle:Vin'.':empty.html.twig');
            }

            if (count($result) > 1)
            {
                unset ($result['Bmw']);
            }

            foreach ($result as $index=>$value)
            {
                    $markForRender = $index;
            }

            if (!in_array($markForRender, $marksVAG)) {

                return $this->redirect(
                    $this->generateUrl('vin_'.strtolower($markForRender).'_result', array('request' => $request)), 307);

            }

            else {
                return $this->redirect(
                    $this->generateUrl('vin_'.strtolower($markForRender).'_region', array('request' => $request)), 307);

            }
        }
    }


}
