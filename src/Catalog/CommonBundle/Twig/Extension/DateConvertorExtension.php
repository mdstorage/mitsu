<?php


namespace Catalog\CommonBundle\Twig\Extension;

use \Twig_Extension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Catalog\BmwBundle\Components\BmwConstants;

class DateConvertorExtension extends Twig_Extension{

    public function getName()
    {
        return 'date_convertor.extension';
    }

    public function getFilters() {
        return array(
            'date_convertor'   => new \Twig_Filter_Method($this, 'dateConvertor'),
            'file_exists'   => new \Twig_Filter_Method($this, 'fileExists')
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'set_locale',array($this, 'setLocale')
            ),
            new \Twig_SimpleFunction(
                'trans',array($this, 'translate_en')
            ),
        );
    }

    public function dateConvertor($str) {
        if ($str == "" || $str == "00000000" || $str == "99999999") {
            return "...";
        }
        return (substr( $str, 0, 4)."/".substr($str, 4, 2)."/".substr($str, 6, 2));
    }

    public function fileExists($file) {

        if (file_exists($file)) {
            return true;
        }

        return false;
    }

    public function setLocale($request, $locale) {

        $localeSet = ($locale == 'ru')?'ru_RU':'en_EN';

        $request->setLocale($localeSet);

    }

    public function translate_en($name, $locale)
    {
        $tr_en = '/../../../../../app/Resources/translations/bmw.en.yml';
        $tr_ru = '/../../../../../app/Resources/translations/bmw.ru.yml';

        $tr = ($locale == 'ru') ? $tr_ru : $tr_en;

        $translator = new Translator($locale);
        $translator->addLoader('yaml', new YamlFileLoader());
        $translator->addResource('yaml', __DIR__.$tr, $locale, BmwConstants::TRANSLATION_DOMAIN);

        return $translator->trans($name, array(), BmwConstants::TRANSLATION_DOMAIN);
    }
} 