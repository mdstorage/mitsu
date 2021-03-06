<?php
namespace Catalog\SubaruBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;


class ComplectationType extends AbstractType{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['data'] as $index=>$value) {


                $builder
                    ->add('title'.$index, 'choice', array(
                        'label' => $value['options']['option1'][$index],
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