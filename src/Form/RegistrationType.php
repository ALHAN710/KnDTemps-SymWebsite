<?php

namespace App\Form;

use App\Entity\Team;
use App\Entity\User;
use App\Entity\Office;
use App\Entity\Enterprise;
//use Symfony\Component\Form\AbstractType;
use App\Form\ApplicationType;
use App\Entity\PointingLocation;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegistrationType extends ApplicationType
{
    private $entId;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->entId = $options['entId'];
        $builder
            ->add(
                'firstName',
                TextType::class,
                $this->getConfiguration("Prénom (*)", "Veuillez Entrer le Prénom svp (Obligatoire)...")
            )
            ->add(
                'lastName',
                TextType::class,
                $this->getConfiguration("Nom (*)", "Veuillez Entrer le Nom svp (Obligatoire)...")
            )
            ->add(
                'email',
                EmailType::class,
                $this->getConfiguration("Email", "Veuillez Entrer l'adresse Email...", ['required' => false])
            )
            ->add(
                'phoneNumber',
                TextType::class,
                $this->getConfiguration("N° Tel (*) :", "Numéro de téléphone svp (Obligatoire)...")
            )
            ->add(
                'avatar',
                FileType::class,
                $this->getConfiguration(
                    "Avatar Utilisateur (IMG file)",
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
                'grade',
                ChoiceType::class,
                [
                    'choices' => [
                        'M.'    => 'M.',
                        'Mme.'  => 'Mme.',

                    ],
                    'label'    => 'Titre'
                ]
            )
            /*->add(
                'attribut',
                ChoiceType::class,
                [
                    'choices' => [
                        'Surbordonné'   => 'Subordinate',
                        "Chef d'équipe" => 'Leader',

                    ],
                    'label'    => 'Rôle'
                ]
            )*/
            ->add(
                'registrationNumber',
                TextType::class,
                $this->getConfiguration("N° Matricule :", "Numéro du Matricule svp...", [
                    'required'  => false,
                ])
            )
            //->add('commission')
            ->add(
                'hiringAt',
                DateTimeType::class,
                $this->getConfiguration("Date d'Embauche", "", [
                    'widget' => "single_text",
                    'required' => false
                ])
            )
            ->add(
                'bornAt',
                DateTimeType::class,
                $this->getConfiguration("Date de Naissance", "", [
                    'widget' => "single_text",
                    'required' => false
                ])
            )
            ->add(
                'sex',
                ChoiceType::class,
                [
                    'choices' => [
                        'Masculin' => 'Male',
                        "Féminin"  => 'Female',

                    ],
                    'label'    => 'Sexe'
                ]
            )

            /*->add(
                'pointingLocation',
                EntityType::class,
                [
                    // looks for choices from this entity
                    'class' => PointingLocation::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('point')
                            ->innerJoin('point.enterprise', 'e')
                            ->where('e.id = :entId')
                            ->setParameters(array(
                                'entId'    => $this->entId,

                            ));
                        //->orderBy('u.username', 'ASC');
                    },
                    // uses the User.username property as the visible option string
                    'choice_label' => 'name',

                    // used to render a select box, check boxes or radios
                    // 'multiple' => true,
                    // 'expanded' => true,
                    'label'    => 'Zone de Pointage'
                ]
            )*/;
        if (!$options['isEdit']) {
            $builder
                ->add(
                    'hash',
                    PasswordType::class,
                    $this->getConfiguration("Password (*)", "Please enter your password...")
                )
                ->add(
                    'passwordConfirm',
                    PasswordType::class,
                    $this->getConfiguration("Confirmation de mot de passe (*)", "Veuillez confirmer votre mot de passe")
                );
        }
        if ($options['isSupAdmin']) {
            $builder
                ->add(
                    'enterprise',
                    EntityType::class,
                    [
                        // looks for choices from this entity
                        'class' => Enterprise::class,
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
                        'choice_label' => 'socialReason',

                        // used to render a select box, check boxes or radios
                        // 'multiple' => true,
                        // 'expanded' => true,
                    ]
                )
                ->add(
                    'role',
                    ChoiceType::class,
                    [
                        'choices' => [
                            "SELLER"            => 'ROLE_SELLER',
                            "CHEF DU PERSONNEL" => 'ROLE_RH_MANAGER',
                            'ADMINISTRATEUR'    => 'ROLE_ADMIN',

                        ],
                        'label'    => 'Attribut'
                    ]
                );
        }
        if (!$options['isSupAdmin']) {
            $builder
                ->add(
                    'office',
                    EntityType::class,
                    [
                        // looks for choices from this entity
                        'class' => Office::class,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('off')
                                ->innerJoin('off.enterprise', 'e')
                                ->where('e.id = :entId')
                                ->setParameters(array(
                                    'entId'    => $this->entId,

                                ));
                            //->orderBy('u.username', 'ASC');
                        },
                        // uses the User.username property as the visible option string
                        'choice_label' => 'name',

                        // used to render a select box, check boxes or radios
                        // 'multiple' => true,
                        // 'expanded' => true,
                        'label'    => 'Poste'
                    ]
                )
                ->add(
                    'team',
                    EntityType::class,
                    [
                        // looks for choices from this entity
                        'class' => Team::class,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('team')
                                ->innerJoin('team.enterprise', 'e')
                                ->where('e.id = :entId')
                                ->setParameters(array(
                                    'entId'    => $this->entId,

                                ));
                            //->orderBy('u.username', 'ASC');
                        },
                        // uses the User.username property as the visible option string
                        'choice_label' => 'name',

                        // used to render a select box, check boxes or radios
                        // 'multiple' => true,
                        // 'expanded' => true,
                        'label'    => 'Equipe'
                    ]
                )
                ->add(
                    'role',
                    ChoiceType::class,
                    [
                        'choices' => [
                            'SUBORDONNÉ'        => 'ROLE_USER',
                            "CHEF D'ÉQUIPE"     => 'ROLE_LEADER',
                            "CHEF DU PERSONNEL" => 'ROLE_RH_MANAGER',
                            'ADMINISTRATEUR'    => 'ROLE_ADMIN',

                        ],
                        'label'    => 'Attribut'
                    ]
                );
        }
            /*
            ->add('wtperday')
            ->add('hash')
            ->add('enterprise')
            ->add('countryCode')
            ->add('statut')
            ->add('roles')
            ->add('createdAt')
            ->add('userRoles')
            ->add('dismissedAt')
            ->add('verificationCode')
            ->add('verified')
            */;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'entId'      => 0,
            'isSupAdmin' => false,
            'isEdit'     => false,
        ]);
    }
}
