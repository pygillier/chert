<?php

namespace pygillier\Chert\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints as Assert;

class LinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', UrlType::class, array(
                'constraints' => new Assert\Url(),
                'attr' => array(
                    'placeholder' => "http://mylongaddress.com"
                )

            ))
            ->add('submit', SubmitType::class, array(
                'label' => "Minify !",
                'attr' => array(
                    'class' => 'btn btn-b smooth'
                )
            ))
        ;
    }

    public function getName()
    {
        return 'link';
    }
}