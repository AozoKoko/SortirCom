<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use phpDocumentor\Reflection\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Sortie;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class ,['attr' => array('class' => 'uk-textarea')] )
            ->add('dateHeureDebut', DateTimeType::class, ['widget'=>'single_text',
                'attr' => array('class' => 'uk-textarea'),])
            ->add('duree', NumberType::class,['attr' => array('class' => 'uk-input')],)
            ->add('dateLimiteInscription',DateTimeType::class, ['widget'=>'single_text',
                'attr' => array('class' => 'uk-textarea'),])
            ->add('nbInscriptionsMax', NumberType::class,['attr' => array('class' => 'uk-input')])
            ->add('infosSortie',TextareaType::class,['attr' => array('class' => 'uk-textarea')])
            ->add('lieu',EntityType::class,['class' => Lieu::class,
                'attr' => array('class' => 'uk-button uk-form-select')])

        ;
    }

//    public function formModify(FormBuilderInterface $builder, array $options):void{
//        $builder
//            ->add('nom')
//            ->add('dateHeureDebut')
//            ->add('dateLimiteInscription')
//            -add()
//    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
