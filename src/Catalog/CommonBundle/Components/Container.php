<?php
namespace Catalog\CommonBundle\Components;

use Catalog\CommonBundle\Components\Interfaces\ArticulInterface;
use Catalog\CommonBundle\Components\Interfaces\CollectionInterface;
use Catalog\CommonBundle\Components\Interfaces\ComplectationInterface;
use Catalog\CommonBundle\Components\Interfaces\GroupInterface;
use Catalog\CommonBundle\Components\Interfaces\ModelInterface;
use Catalog\CommonBundle\Components\Interfaces\ModificationInterface;
use Catalog\CommonBundle\Components\Interfaces\RegionInterface;
use Catalog\CommonBundle\Components\Interfaces\SchemaInterface;

class Container {
    private $regions = array();
    private $groups = array();
    private $schemas = array();
    private $options;

    private $activeRegion;
    private $activeModel;
    private $activeModificataion;
    private $activeComplectation;

    private $activeGroup;
    private $activeSchema;
    private $activePnc;
    private $activeArticul;

    public function setActiveArticul(ArticulInterface $articulClass)
    {
        $this->activeArticul = $articulClass;

        return $this;
    }

    public function setRegions(CollectionInterface $collection)
    {
        $this->regions = $collection->getCollection();

        return $this;
    }

    public function getActiveArticul()
    {
        return $this->activeArticul;
    }

    public function getRegions()
    {
        return $this->regions;
    }

    public function removeRegion($code)
    {
        unset($this->regions[$code]);
    }

    public function setActiveRegion(RegionInterface $region)
    {
        $this->activeRegion = $region;

        return $this;
    }

    public function getActiveRegion()
    {
        return $this->activeRegion;
    }

    public function setGroups(CollectionInterface $collection)
    {

        $this->groups = $collection->getCollection();

        return $this;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function getGroup($code)
    {
        if(isset($this->groups[$code]))
        return $this->groups[$code];
    }

    public function removeGroup($code)
    {
        unset($this->groups[$code]);

        return $this;
    }

    public function setActiveGroup(GroupInterface $oGroup)
    {
        $this->activeGroup = $oGroup;

        return $this;
    }

    public function getActiveGroup()
    {
        return $this->activeGroup;
    }

    public function setActiveModification(ModificationInterface $oModification)
    {
        $this->activeModification = $oModification;

        return $this;
    }

    public function getActiveModification()
    {
        return $this->activeModification;
    }

    public function setActiveModel(ModelInterface $oModel)
    {
        $this->activeModel = $oModel;

        return $this;
    }

    public function getActiveModel()
    {
        return $this->activeModel;
    }

    public function setSchemas(CollectionInterface $collection)
    {
        $this->schemas = $collection->getCollection();

        return $this;
    }

    public function getSchemas()
    {
        return $this->schemas;
    }

    public function removeSchema($code)
    {
        unset($this->schemas[$code]);

        return $this;
    }

    public function setActiveSchema(SchemaInterface $oSchema)
    {
        $this->activeSchema = $oSchema;

        return $this;
    }

    public function getActiveSchema()
    {
        return $this->activeSchema;
    }

    public function setActivePnc($oPnc)
    {
        $this->activePnc = $oPnc;

        return $this;
    }

    public function getActivePnc()
    {
        return $this->activePnc;
    }

    public function getActiveComplectation()
    {
        return $this->activeComplectation;
    }

    public function setActiveComplectation(ComplectationInterface $oComplectation)
    {
        $this->activeComplectation = $oComplectation;

        return $this;
    }
} 