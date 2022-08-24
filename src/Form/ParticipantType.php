<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Participant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo')
            ->add('prenom')
            ->add('nom')
            ->add('user', EntityType::class, [
                'class' => User::class,
                'suuu' => function($user) {
                    return $user->getEmail();
                }
            ])
            ->add('telephone')
           /* ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => function($campus) {
                    return $campus->getNom();
                }
            ])*/

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
