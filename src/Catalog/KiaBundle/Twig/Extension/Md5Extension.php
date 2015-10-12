<?php
/**
 * Created by PhpStorm.
 * User: misha
 * Date: 10.01.15
 * Time: 23:20
 */

namespace Catalog\CommonBundle\Twig\Extension;

use \Twig_Extension;

class Md5Extension extends Twig_Extension{

    public function getName()
    {
        return 'md5.extension';
    }

    public function getFilters() {
        return array(
            'md5'   => new \Twig_Filter_Method($this, 'md'),
        );
    }

    public function md($str) {
        return md5($str);
    }
} 