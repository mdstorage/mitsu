<?php
namespace Catalog\VolvoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;


class ComplectationType extends AbstractType{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['data'] as $index=>$value) {

            switch ($index)
            {
                case 'EN': $label = 'Тип двигателя';break;
                case 'KP': $label = 'Коробка передач';break;
                case 'RU': $label = 'Рулевое управление';break;
                case 'TK': $label = 'Тип кузова';break;
                default: $label = 'Спецтранспорт';

            }




                $builder
                    ->add('title' . $index, 'choice', array(
                        'label' => $label,
                        'choices' => $value['name'],
                        'attr' => array(
                            'class' => 'form-control',
                            'placeholder' => 'Содержание статьи',

                        )
                    ));

            }

    }
    public function getName()
    {
        return 'ComplectationType';
    }
} 