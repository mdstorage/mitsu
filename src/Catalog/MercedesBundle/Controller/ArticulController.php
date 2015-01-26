<?php
namespace Catalog\MercedesBundle\Controller;

use Assetic\Filter\GoogleClosure\BaseCompilerFilter;
use Catalog\CommonBundle\Controller\ArticulController as BaseController;
use Symfony\Component\HttpFoundation\Request;

class ArticulController extends BaseController{
    public function bundle()
    {
        return 'CatalogMercedesBundle:Articul';
    }

    public function model()
    {
        return $this->get('mercedes.articul.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\MercedesBundle\Components\MercedesConstants';
    }
} 