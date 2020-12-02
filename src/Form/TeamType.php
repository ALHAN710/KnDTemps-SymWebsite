<?php

namespace App\Form;

use App\Entity\Team;
use App\Entity\User;
//use Symfony\Component\Form\AbstractType;
use App\Form\UserCollectionType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class TeamType extends ApplicationType
{
    private $entId;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->entId = $options['entId'];
        $builder
            ->add(
                'name',
                TextType::class,
                $this->getConfiguration("Nom (*)", "Nom de l'Équipe svp (Obligatoire)...")
            )
            ->add(
                'responsible',
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
                    'label'    => 'Responsable'
                ]
            )
            /*->add(
                'users',
                CollectionType::class,
                [
                    'entry_type'    => UserCollectionType::class,
                    'allow_add'     => true,
                    'allow_delete'  => true,
                    'entry_options' => array(
                        'isEdit' => $options['isEdit'],
                        'entId'  => $options['entId']
                    ),
                ]
            )*/
            //->add('enterprise')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
            'entId'      => 0,
            'isEdit'     => false,
        ]);
    }
}
