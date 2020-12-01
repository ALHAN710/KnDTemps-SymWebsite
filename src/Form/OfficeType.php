<?php

namespace App\Form;

use App\Entity\Office;
//use Symfony\Component\Form\AbstractType;
use App\Form\ApplicationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class OfficeType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                $this->getConfiguration("Nom (*)", "Nom du Poste svp (Obligatoire)...")
            )
            ->add(
                'hourlySalary',
                NumberType::class,
                $this->getConfiguration("Salaire horaire (XAF)", "Entrer le salaire horaire...", ['required' => false])
            )
            ->add(
                'monthlySalary',
                NumberType::class,
                $this->getConfiguration("Salaire Mensuel (XAF)", "Entrer le salaire mensuel...", ['required' => false])
            )
            //->add('enterprise')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Office::class,
        ]);
    }
}
