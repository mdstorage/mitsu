<?php
namespace Catalog\CommonBundle\Controller;


use Catalog\CommonBundle\Components\Constants;
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

            $result = $this->model()->getVinFinderResult($vin);
            if (!$result) {
                return $this->render($this->bundle().':empty.html.twig');
            }

            setcookie(Constants::PROD_DATE, $result[Constants::PROD_DATE]);
            return $this->render($this->bundle().':02_result.html.twig', array(
                'result' => $result
            ));
        }
    }
}