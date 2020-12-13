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
                $this->getConfiguration("Raison Sociale ou Nom de l'entreprise*", "Entrer la raison sociale ou nom")
            )
            ->add(
                'niu',
                TextType::class,
                $this->getConfiguration("Numéro d'Iddentification Unique de l'entreprise", "Entrer le numéro d'identification unique(NIU)...", ['required' => false])
            )
            ->add(
                'rccm',
                TextType::class,
                $this->getConfiguration("Registre du Commerce de l'entreprise", "Entrer le RCCM...", ['required' => false])
            )
            ->add(
                'address',
                TextType::class,
                $this->getConfiguration("Adresse du Siège Sociale", "Adresse siège sociale...")
            )
            ->add(
                'phoneNumber',
                TextType::class,
                $this->getConfiguration("Numéro de téléphone de l'entreprise*", "Numéro de téléphone(Obligatoire)...")
            )
            ->add(
                'email',
                EmailType::class,
                $this->getConfiguration("Email de l'entreprise*", "Email...")
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
                'timeZone',
                ChoiceType::class,
                [
                    'choices' => [
                        'GMT'          => '0',
                        'GMT+1:00'     => '1',
                        'GMT+2:00'     => '2',
                        'GMT+3:00'     => '3',
                        'GMT+3:30'     => '3.5',
                        'GMT+4:00'     => '4',
                        'GMT+5:00'     => '5',
                        'GMT+5:30'     => '5.3',
                        'GMT+5:45'     => '5.75',
                        'GMT+6:00'     => '6',
                        'GMT+6:30'     => '6.5',
                        'GMT+7:00'     => '7',
                        'GMT+8:00'     => '8',
                        'GMT+8:45'     => '8.75',
                        'GMT+9:00'     => '9',
                        'GMT+9:30'     => '9.5',
                        'GMT+10:00'    => '10',
                        'GMT+10:30'    => '10.5',
                        'GMT+11:00'    => '11',
                        'GMT+12:00'    => '12',
                        'GMT+12:45'    => '12.75',
                        'GMT+13:00'    => '13',
                        'GMT+14:00'    => '14',
                        'GMT-1:00'     => '-1',
                        'GMT-2:00'     => '-2',
                        'GMT-3:00'     => '-3',
                        'GMT-3:30'     => '-3.5',
                        'GMT-4:00'     => '-4',
                        'GMT-5:00'     => '-5',
                        'GMT-6:00'     => '-6',
                        'GMT-7:00'     => '-7',
                        'GMT-8:00'     => '-8',
                        'GMT-9:00'     => '-9',
                        'GMT-9:30'     => '-9.5',
                        'GMT-10:00'    => '-10',
                        'GMT-11:00'    => '-11',
                        'GMT-12:00'    => '-12',
                    ],

                    'label'    => 'Fuseau Horaire'
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
