<?php

namespace Catalog\CommonBundle\Components\Interfaces;


interface CommonInterface {

    public function setCode($code);
    public function getCode();

    public function setName($name);
    public function getRuname();

    public function setOptions(array $options);
    public function addOption($name, $value);
    public function getOption($name);
    public function getOptions();

} 