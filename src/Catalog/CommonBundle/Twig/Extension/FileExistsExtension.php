<?php

namespace Catalog\CommonBundle\Twig\Extension;


class FileExistsExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'file_exists.extension';
    }

    public function getFilters() {
        return array(
            'file_exists'   => new \Twig_Filter_Method($this, 'fileExists'),
        );
    }

    public function fileExists($file) {

        if (file_exists($file)) {
            return true;
        }
        else
        {
            return false;
        }

    }
} 