<?php

namespace App\Form;

use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TriSortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Campus', EntityType::class, [
                'class' => Campus::class,
                'placeholder' => 'Tous les campus',
                'required' => false,
                'choice_label' => function ($campus) {
                    return $campus->getNom();
                },
                'attr' => array('class' => 'uk-button uk-flex-center uk-form-select'), 'label' => false
            ])
            ->add('BetweenDate1', DateTimeType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'uk-textarea'
                ],
                'required' => false,
                'label'=>false
            ])
            ->add('BetweenDate2', DateTimeType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'uk-textarea'
                ],
                'required' => false,
                'label' => false,
            ])
            //->add('nomSortie')
            ->add('submit', SubmitType::class, array(
                'label' => 'Submit',
                'attr' => array(
                    'class' => 'uk-button uk-button-secondary uk-flex-center'
                )
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
