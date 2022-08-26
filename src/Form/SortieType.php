<?php

namespace App\Form;

use phpDocumentor\Reflection\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Sortie;
use function Sodium\add;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('dateHeureDebut', DateTimeType::class, ['widget'=>'single_text'])
            ->add('duree')
            ->add('dateLimiteInscription',DateTimeType::class, ['widget'=>'single_text'])
            ->add('nbInscriptionsMax')
            ->add('infosSortie')
            ->add('lieu')
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
