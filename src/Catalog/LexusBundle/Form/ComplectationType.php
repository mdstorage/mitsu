<?php
namespace Catalog\LexusBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Catalog\CommonBundle\Components\Constants;


class ComplectationType extends AbstractType{

    public function buildForm(FormBuilderInterface $builder, array $aOptions)
    {
        $aLabels = array();
        foreach ($aOptions['data'] as $index=>$value) {

            $aLabels = array_diff($value['options']['option1'], array(''));


                $builder
                    ->add('title'.$index, 'choice', array(
                        'label' => $aLabels[$index],
                        'choices' => $value['name'],

                        'attr' => array(
                            'class' => 'form-control',

                        )
                    ));

            }

    }
    public function getName()
    {
        return 'ComplectationType';
    }
} 