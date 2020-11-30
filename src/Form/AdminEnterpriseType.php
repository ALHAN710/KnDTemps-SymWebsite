<?php

namespace App\Form;

use App\Entity\Enterprise;
use App\Entity\Subscription;
use App\Form\ApplicationType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AdminEnterpriseType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'socialReason',
                TextType::class,
                $this->getConfiguration("Raison Social ou Nom (*)", "Entrer la raison sociale ou nom (Obligatoire))")
            )
            ->add(
                'niu',
                TextType::class,
                $this->getConfiguration("NIU", "Entrer le numéro d'idantification unique(NIU)...", ['required' => false])
            )
            ->add(
                'rccm',
                TextType::class,
                $this->getConfiguration("RCCM", "Entrer le RCCM...", ['required' => false])
            )
            ->add(
                'address',
                TextType::class,
                $this->getConfiguration("Adresse (*)", "Adresse siège sociale (Obligatoire)...")
            )
            ->add(
                'phoneNumber',
                TextType::class,
                $this->getConfiguration("Tél (*)", "Numéro de téléphone (Obligatoire)...")
            )
            ->add(
                'email',
                EmailType::class,
                $this->getConfiguration("Email", "Email...", ['required' => false])
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
                $this->getConfiguration("TVA (*)", "TVA appliquée (Obligatoire)...", [
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
                $this->getConfiguration("Description", "Brève Description de l'entreprise du client...", ['required' => false])
            )
            ->add(
                'town',
                TextType::class,
                $this->getConfiguration("Ville (*)", "Entrer la ville du client (Obligatoire)...")
            )
            /*->add('subscribeAt')
            ->add('createdAt')
            ->add('businesscontacts')*/;

        if (!$options['isEdit']) {
            $builder
                ->add(
                    'subscriptionDuration',
                    ChoiceType::class,
                    [
                        'choices' => [
                            '1 Mois'  => '1',
                            '6 Mois'  => '6',
                            '12 Mois' => '12',

                        ],
                        'label'    => 'Durée'
                    ]
                )
                ->add(
                    'subscription',
                    EntityType::class,
                    [
                        // looks for choices from this entity
                        'class' => Subscription::class,
                        /*'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('p')
                            ->innerJoin('p.enterprise', 'e')
                            ->where('e.id = :entId')
                            ->andWhere('p.hasStock = 1')
                            ->setParameters(array(
                                'entId'    => $this->entId,

                            ));
                        //->orderBy('u.username', 'ASC');
                    },*/
                        // uses the User.username property as the visible option string
                        'choice_label' => 'name',
                        'label' => 'Choisir un Abonnement'
                        // used to render a select box, check boxes or radios
                        // 'multiple' => true,
                        // 'expanded' => true,
                    ]
                )
                /*->add(
                    'tarifs',
                    EntityType::class,
                    [
                        // looks for choices from this entity
                        'class' => Subscription::class,
                        
                        // uses the User.username property as the visible option string
                        'choice_label' => 'tarifs',

                        // used to render a select box, check boxes or radios
                        // 'multiple' => true,
                        // 'expanded' => true,
                    ]
                )*/
                ->add(
                    'subscriptionPrice',
                    TextType::class,
                    $this->getConfiguration("Prix (XAF)", "", [
                        'required' => false,
                        'attr' => [
                            'readonly' => true,
                        ]
                    ])
                );
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Enterprise::class,
            'isEdit'     => false,
        ]);
    }
}
