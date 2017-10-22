<?php

namespace Acme\TaskBundle\Controller;

use Acme\VinBundle\Controller\VinController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $marks = VinController::MARKS;
        sort($marks);
        return $this->render('AcmeTaskBundle:Default:index.html.twig', ['marks' => $marks]);
    }

    public function resultAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $vin = $request->get('vin');

            $result = [];
            foreach (VinController::MARKS as $mark) {
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
                if (!empty($result)) {
                    break;
                }
            }
            if (!$result) {
                return $this->render('CatalogBmwBundle:Vin' . ':empty.html.twig');
            }

            $markForRender = key($result);

            if ($markForRender && !in_array($markForRender, VinController::VAG)) {
                return $this->redirect(
                    $this->generateUrl('vin_' . strtolower($markForRender) . '_result', ['request' => $request]), 307);
            } else {
                return $this->redirect(
                    $this->generateUrl('vin_' . strtolower($markForRender) . '_region', ['request' => $request]), 307);
            }
        }
    }
}
