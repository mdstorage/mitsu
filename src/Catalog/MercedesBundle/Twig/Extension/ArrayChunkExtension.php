<?php

namespace Catalog\MercedesBundle\Twig\Extension;

use Catalog\MercedesBundle\Components\MercedesConstants;
use Twig_Extension;

class ArrayChunkExtension extends Twig_Extension
{

    public function getName()
    {
        return 'mercedes_array_chunk.extension';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'array_chunk', [$this, 'arrayChunk']
            ),
            new \Twig_SimpleFunction(
                'models_array', [$this, 'getModelsArray']
            ),
        ];
    }

    public function arrayChunk($array, $size)
    {
        return array_chunk($array, ceil(count($array) / $size), true);
    }

    public function getModelsArray()
    {
        return MercedesConstants::$ARRAY_OF_MODELS;
    }
}
