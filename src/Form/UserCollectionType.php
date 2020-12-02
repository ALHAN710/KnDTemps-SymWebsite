<?php

namespace App\Form;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserCollectionType extends AbstractType
{
    private $entId;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->entId  = $options['entId'];
        $this->isEdit = $options['isEdit'];
        $builder
            ->add(
                'user_',
                EntityType::class,
                [
                    // looks for choices from this entity
                    'class' => User::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->innerJoin('u.enterprise', 'e')
                            ->where('e.id = :entId')
                            ->setParameters(array(
                                'entId'    => $this->entId,

                            ));
                        //->orderBy('u.username', 'ASC');
                    },
                    // uses the User.username property as the visible option string
                    'choice_label' => function ($user) {
                        return $user->getGrade() . ' ' . $user->getFullName();
                    },

                    // used to render a select box, check boxes or radios
                    // 'multiple' => true,
                    // 'expanded' => true,
                    //'label'    => 'Responsable'
                ]
            )
            /*->add('lastName')
            ->add('email')
            ->add('roles')
            ->add('hash')
            ->add('phoneNumber')
            ->add('countryCode')
            ->add('verificationCode')
            ->add('verified')
            ->add('createdAt')
            ->add('avatar')
            ->add('grade')
            ->add('wtperday')
            ->add('registrationNumber')
            ->add('commission')
            ->add('statut')
            ->add('hiringAt')
            ->add('bornAt')
            ->add('sex')
            ->add('dismissedAt')
            ->add('userRoles')
            ->add('office')
            ->add('enterprise')
            ->add('team')
            ->add('pointingLocation')*/;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'entId'      => 0,
            'isEdit'     => false,
        ]);
    }
}
