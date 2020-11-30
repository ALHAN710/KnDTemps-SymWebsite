<?php

namespace App\Form;

use App\Entity\Subscription;
use App\Form\ApplicationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AdminSubscriptionType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'subscriptionName',
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
                'subscriptionPrice',
                TextType::class,
                $this->getConfiguration("Prix (XAF)", "", [
                    'required' => false,
                    'attr' => [
                        'readonly' => true,
                    ]
                ])
            )
            /*->add('sheetNumber')
            ->add('productRefNumber')
            ->add('tarifs')*/;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Subscription::class,
        ]);
    }
}
