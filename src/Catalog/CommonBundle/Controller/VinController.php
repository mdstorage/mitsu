<?php
namespace Catalog\CommonBundle\Controller;


use Catalog\CommonBundle\Components\Constants;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class VinController extends CatalogController{

    public function indexAction()
    {
        $headers = $this->get('request')->server->getHeaders();

        foreach($this->get('request')->cookies->keys() as $index => $value)
        {
            if (!empty($headers['REFERER'])){
                if (stripos($headers['REFERER'], 'vincat.ru') == false){
                    if (stripos($value, Constants::COOKIEHOST) !== false)
                    {
                        setcookie($value, "");
                    }
                }
            }
        }
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

            $brandSlash = $request->server->get('REQUEST_URI');


            $brand = explode('/', $brandSlash)[1];


            $callbackhost = trim($request->get('callbackhost'));



            $domain = trim($request->get('domain'));

            $domain = substr_replace($domain, '', strpos($domain, '.'), 1);



            $headers = $request->server->getHeaders();

            if (stripos($headers['REFERER'], 'callbackhost=') || stripos($headers['REFERER'], 'modelCode'))
            {
                if (!$call = $request->cookies->get(Constants::COOKIEHOST.$brand.urlencode(str_replace('.', '', $domain))))
                {
                    if ($callbackhost){
                        setcookie(Constants::COOKIEHOST.$brand.urlencode(str_replace('.', '', $domain)), $callbackhost);
                    }
                }
            }
            else{
                setcookie(Constants::COOKIEHOST.$brand.urlencode(str_replace('.', '', $domain)), "");
            }

            if (stripos($headers['REFERER'], 'domain')|| stripos($headers['REFERER'], 'modelCode'))
            {
                if (!$call = $request->cookies->get('DOMAIN'))
                {
                    if ($domain){
                        setcookie('DOMAIN', str_replace('.', '', $domain));
                    }

                }
            }
            else {
                setcookie('DOMAIN', "");
            }

            return $this->render($this->bundle().':02_result.html.twig', array(
                'result' => $result
            ));
        }
    }
}