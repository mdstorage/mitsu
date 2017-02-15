<?php
namespace Catalog\HyundaiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Catalog\CommonBundle\Components\Constants;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;


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
                            'id' => $index

                        )
                    ))
                    /*->add('save', 'button', array(
                        'attr' => array('class' => 'save'),
                    ))*/;





            }

    }
    public function getName()
    {
        return 'ComplectationType';
    }
} 