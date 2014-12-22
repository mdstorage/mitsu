<?php
namespace Catalog\CommonBundle\Components\Interfaces;

interface SchemaInterface extends CommonInterface
{
    public function setPncs($pncs, PncInterface $pncClass);
    public function getPncs();

    public function setCommonArticuls($articuls, ArticulInterface $articulClass);
    public function getCommonArticuls();

    public function setRefGroups($groups, GroupInterface $groupClass);
    public function getRefGroups();
}