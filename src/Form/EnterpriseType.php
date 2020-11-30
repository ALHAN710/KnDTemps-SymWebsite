<?php

namespace App\Form;

use App\Entity\Enterprise;
use App\Form\ApplicationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class EnterpriseType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'socialReason',
                TextType::class,
                $this->getConfiguration("(*)", "Entrer la raison sociale ou nom(Obligatoire)")
            )
            ->add(
                'niu',
                TextType::class,
                $this->getConfiguration("", "Entrer le numéro d'idantification unique(NIU)...", ['required' => false])
            )
            ->add(
                'rccm',
                TextType::class,
                $this->getConfiguration("", "Entrer le RCCM...", ['required' => false])
            )
            ->add(
                'address',
                TextType::class,
                $this->getConfiguration("", "Adresse siège sociale...")
            )
            ->add(
                'phoneNumber',
                TextType::class,
                $this->getConfiguration("(*)", "Numéro de téléphone(Obligatoire)...")
            )
            ->add(
                'email',
                EmailType::class,
                $this->getConfiguration("Email (*)", "Email...")
            )
            ->add(
                'logo',
                FileType::class,
                $this->getConfiguration(
                    "logo (IMG file)",
                    "",
                    [
                        // unmapped means that this field is not associated to any entity property
                        'mapped' => false,

                        // make it optional so you don't have to re-upload the IMG file
                        // every time you edit the Product details
                        'required' => false,

                        // unmapped fields can't define their validation using annotations
                        // in the associated entity, so you can use the PHP constraint classes
                        'constraints' => [
                            new File([
                                'maxSize' => '1024k',
                                'mimeTypes' => [
                                    'image/png',
                                    'image/jpeg',
                                ],
                                'mimeTypesMessage' => 'Please upload a valid Image format(jpeg, png)',
                            ])
                        ],
                    ]
                )
            )
            ->add(
                'tva',
                NumberType::class,
                $this->getConfiguration("TVA", "TVA appliqué...", [
                    'attr' => [
                        'min'   => '0',
                    ]
                ])
            )
            ->add(
                'country',
                ChoiceType::class,
                [
                    'choices' => [
                        'Cameroun' => 'Cameroun',
                        //'STOCK MANAGER' => 'ROLE_STOCK_MANAGER',
                        //'ADMIN' => 'ROLE_ADMIN',

                    ],
                    'label'    => 'Pays'
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                $this->getConfiguration("Description", "Brève Description de l'entreprise...", ['required' => false])
            )
            /*->add('createdAt')
            ->add('businesscontacts')
            ->add('subscription')
            ->add('subscriptionDuration')
            ->add('subscribeAt')*/;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Enterprise::class,
        ]);
    }
}
