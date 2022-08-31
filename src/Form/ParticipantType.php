<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('pseudo', textType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a username',
                    ]),
                    new Length([
                        'max' => 20,
                    ]),
                ]
                ,'attr' => array('class' => 'uk-textarea')])
            ->add('prenom', textType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a first name',
                    ]),
                    new Length([
                        'max' => 20,
                    ]),
                ],
                'attr' => array('class' => 'uk-textarea')
            ])
            ->add('nom', textType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a name',
                    ]),
                    new Length([
                        'max' => 30,
                    ]),
                ],
                'attr' => array('class' => 'uk-textarea')
            ])
            ->add('telephone', numberType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a telephone number',
                    ]),
                    new Length([
                        'max' => 10,
                    ]),
                ],
                'attr' => array('class' => 'uk-input')
            ])
            ->add('user', UserType::class,['label'   => false])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => function ($campus) {
                    return $campus->getNom();
                },
                'attr' => array('class' => 'uk-input')
            ])
            ->add('submit', SubmitType::class, ['attr' => array('class' => 'uk-button uk-form-select')]);
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
