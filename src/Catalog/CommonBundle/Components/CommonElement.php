<?php
namespace Catalog\CommonBundle\Components;


use Catalog\CommonBundle\Components\Interfaces\CommonInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;

abstract class CommonElement implements CommonInterface{

    private $code;
    private $name;

    private $options;

    private $constants;

    public function setConstants($constants)
    {
        $this->constants = $constants;
    }

    public function getConstants()
    {
        return $this->constants;
    }

    public function setCode($code)
    {
        $this->code = (string) $code;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRuname()
    {
        return $this->translate($this->name);
    }

    public function getCode()
    {
        return (string) $this->code;
    }

    public function setOptions(array $options=array())
    {
        foreach($options as $name=>$value){
            $this->addOption($name, $value);
        }

        return $this;
    }

    public function addOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    public function hasOption($name)
    {
        if($this->options[$name]){
            return true;
        } else {
            return false;
        }
    }

    public function getOption($name)
    {
        if($this->hasOption($name)){
            return $this->options[$name];
        }
    }

    public function getOptions()
    {
        return $this->options;
    }

    private function translate($name)
    {
        $constants = $this->getConstants();

        $translator = new Translator($constants::LOCALE);
        $translator->addLoader('yaml', new YamlFileLoader());
        $translator->addResource('yaml', __DIR__.$constants::TRANSLATION_RESOURCE, $constants::LOCALE, $constants::TRANSLATION_DOMAIN);

        return $translator->trans($name, array(), $constants::TRANSLATION_DOMAIN);
    }
} 