<?php

namespace Catalog\CommonBundle\Twig\Extension;

class FileExistsExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'file_exists.extension';
    }

    public function getFilters()
    {
        return [
            'file_exists' => new \Twig_Filter_Method($this, 'fileExists'),
        ];
    }

    public function fileExists($file)
    {

        $urlHeaders = @get_headers($file);

        if (strpos($urlHeaders[0], '200')) {

            return true;
        } else {

            return false;
        }
    }
} 
