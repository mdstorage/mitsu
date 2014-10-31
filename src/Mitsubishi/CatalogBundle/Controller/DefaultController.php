<?php

namespace Mitsubishi\CatalogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Mitsubishi\CatalogBundle\Entity\Cinfo;
use Mitsubishi\CatalogBundle\Repository;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('MitsubishiCatalogBundle:Models');
        $catalogsList = $repository->getCatalogsList();
        return $this->render('MitsubishiCatalogBundle:Default:index.html.twig', array('catalogsList'=>$catalogsList));
    }

    public function vncsListAction($catalog)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('MitsubishiCatalogBundle:Models');
        $vncsList = $repository->getVncsListByCatalog($catalog);
        return $this->render('MitsubishiCatalogBundle:Default:vncs_list.html.twig', array('vncsList'=>$vncsList));

    }
}
