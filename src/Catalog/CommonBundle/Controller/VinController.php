<?php
namespace Catalog\CommonBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class VinController extends CatalogController{

    public function indexAction()
    {
        return $this->render($this->bundle().':01_index.html.twig');
    }

    public function resultAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {

            $vin = $request->get('vin');
            setcookie('mazdaProdDate');
            $result = $this->model()->getVinFinderResult($vin);
            setcookie('mazdaProdDate', $result['prod_date']);

            if (!$result) {
                return $this->render($this->bundle().':empty.html.twig');
            }

            return $this->render($this->bundle().':02_result.html.twig', array(
                'result' => $result
            ));
        }
    }
}