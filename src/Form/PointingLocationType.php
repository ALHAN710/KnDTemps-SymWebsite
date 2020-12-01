<?php

namespace App\Form;

use App\Form\ApplicationType;
//use Symfony\Component\Form\AbstractType;
use App\Entity\PointingLocation;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class PointingLocationType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'latitude',
                NumberType::class,
                $this->getConfiguration("Latitude (*)", "Coordonnée en Latitude (Obligatoire)...")
            )
            ->add(
                'longitude',
                NumberType::class,
                $this->getConfiguration("Longitude (*)", "Coordonnée en Longitude (Obligatoire)...")
            )
            ->add(
                'ray',
                NumberType::class,
                $this->getConfiguration("Rayon (km)(*)", "Rayon de pointage en km svp (Obligatoire)...", [
                    'attr' => [
                        'min' => 0
                    ]
                ])
            )
            ->add(
                'name',
                TextType::class,
                $this->getConfiguration("Nom (*)", "Nom du Lieu svp (Obligatoire)...")
            )
            //->add('enterprise')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PointingLocation::class,
        ]);
    }
}
