<?php

namespace Acme\TaskBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $marks = [
            'Hyundai',
            'KIA',
            'Honda',
            'Suzuki',
            'Subaru',
            'Mazda',
            'Mercedes',
            'Smart',
            'BMW',
            'Mini',
            'RollsRoyce',
            'Saab',
            'Audi',
            'Volkswagen',
            'Seat',
            'Skoda',
            'HondaEurope',
            'Fiat',
            'FiatProfessional',
            'Lancia',
            'AlfaRomeo',
            'Abarth',
            'Nissan',
            'Infiniti',
            'LandRover',
            'ChevroletUsa',
            'Cadillac',
            'Pontiac',
            'Buick',
            'Hummer',
            'Saturn',
            'GMC',
            'Oldsmobile',
            'Toyota',
            'Lexus',
            'Ford',
            'Mitsubishi',
        ];
        sort($marks);
        reset($marks);
        return $this->render('AcmeTaskBundle:Default:index.html.twig', ['marks' => $marks]);
    }

    public function resultAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $vin      = $request->get('vin');
            $marksVAG = ['Audi', 'Seat', 'Skoda', 'Volkswagen'];

            $result          = [];
            $marks = [
                'Hyundai',
                'KIA',
                'Honda',
                'Subaru',
                'Mazda',
                'Mercedes',
                'Smart',
                'BMW',
                'Mini',
                'RollsRoyce',
                'Saab',
                'Audi',
                'Volkswagen',
                'Seat',
                'Skoda',
                'HondaEurope',
                'Fiat',
                'FiatProfessional',
                'Lancia',
                'AlfaRomeo',
                'Abarth',
                'Nissan',
                'Infiniti',
                'LandRover',
                'ChevroletUsa',
                'Cadillac',
                'Pontiac',
                'Buick',
                'Hummer',
                'Saturn',
                'GMC',
                'Oldsmobile',
                'Toyota',
                'Lexus',
                'Mitsubishi',
            ];

            foreach ($marks as $mark) {
                if ($this->get(strtolower($mark) . '.vin.model')) {
                    if (method_exists($this->get(strtolower($mark) . '.vin.model'), 'getVinRegions')) {
                        $vinFinderResult = $this->get(strtolower($mark) . '.vin.model')->getVinRegions($vin);
                        if (!empty($vinFinderResult)) {
                            $result[$mark] = $vinFinderResult;
                        }
                    } else {
                        $vinFinderResult = $this->get(strtolower($mark) . '.vin.model')->getVinFinderResult($vin);
                        if (!empty($vinFinderResult)) {
                            $result[$mark] = $vinFinderResult;
                        }
                    }
                }
            }
            if (!$result) {
                return $this->render('CatalogBmwBundle:Vin' . ':empty.html.twig');
            }

            if (count($result) > 1) {
                unset ($result['Bmw']);
            }

            foreach ($result as $index => $value) {
                $markForRender = $index;
            }

            if (!empty($markForRender) && !in_array($markForRender, $marksVAG)) {
                return $this->redirect(
                    $this->generateUrl('vin_' . strtolower($markForRender) . '_result', ['request' => $request]), 307);
            } else {
                return $this->redirect(
                    $this->generateUrl('vin_' . strtolower($markForRender) . '_region', ['request' => $request]), 307);
            }
        }
    }
}
