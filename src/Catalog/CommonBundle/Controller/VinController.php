<?php
namespace Catalog\CommonBundle\Controller;


use Catalog\CommonBundle\Components\Constants;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class VinController extends CatalogController{

    public function indexAction()
    {
        /**
         * @deprecated Оставлен для совместимости с маздой
         */
        setcookie(Constants::PROD_DATE, '');

        setcookie(Constants::VIN, '');
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

            /**
             * @deprecated Оставлен для совместимости с маздой
             */
            setcookie(Constants::PROD_DATE, $result[Constants::PROD_DATE]);

            setcookie(Constants::VIN, $vin);
            return $this->render($this->bundle().':02_result.html.twig', array(
                'result' => $result
            ));
        }
    }
}