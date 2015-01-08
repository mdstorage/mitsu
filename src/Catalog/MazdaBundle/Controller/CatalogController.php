<?php
namespace Catalog\MazdaBundle\Controller;


use Catalog\CommonBundle\Controller\CatalogController as BaseController;

class CatalogController extends BaseController{

    public function bundle()
    {
        return 'CatalogMazdaBundle';
    }

    public function model()
    {
        return $this->get('mazda.catalog.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\MazdaBundle\Components\MazdaConstants';
    }

    public function getGroupBySubgroupAction($regionCode, $modelCode, $modificationCode, $subGroupCode)
    {
        $groupCode = $this->model()->getGroupBySubgroup($regionCode, $modelCode, $modificationCode, $subGroupCode);

        return $this->schemasAction($regionCode, $modelCode, $modificationCode, $groupCode, $subGroupCode);
    }
} 