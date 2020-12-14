<?php

namespace App\Form;

use DateTime;
use App\Entity\User;
use App\Entity\Pointing;
use App\Form\ApplicationType;
use Doctrine\ORM\EntityRepository;
//use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class PointingType extends ApplicationType
{
    private $entId;
    private $teamId;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->entId = $options['entId'];
        $this->teamId = $options['teamId'];

        $builder
            ->add(
                'timeIn',
                DateTimeType::class,
                $this->getConfiguration("Heure d'Entrée (*)", "Spécificier l'heure GMT d'entrée svp (Obligatoire)...", [
                    'widget' => 'single_text',
                    'data'   => new \DateTime(),
                    'attr'   => [
                        'min' => (new \DateTime())->format('Y-m-d H:i:s')
                    ]
                ])
            )
            ->add(
                'timeOut',
                DateTimeType::class,
                $this->getConfiguration("Heure de Sortie (*)", "Spécificier l'heure GMT de sortie svp (Obligatoire)...", [
                    'widget' => 'single_text',
                    'data'   => new \DateTime(),
                    'attr'   => [
                        'min' => (new \DateTime())->format('Y-m-d H:i:s')
                    ]
                ])
            )
            //->add('statut')
        ;

        if ($options['entId']) {
            $builder->add(
                'employee',
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
                    'label'    => 'Employé'
                ]
            );
        } else if ($options['teamId']) {
            $builder->add(
                'employee',
                EntityType::class,
                [
                    // looks for choices from this entity
                    'class' => User::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->innerJoin('u.team', 't')
                            ->where('t.id = :teamId')
                            ->setParameters(array(
                                'teamId'    => $this->teamId,

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
                    'label'    => 'Employé'
                ]
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Pointing::class,
            'entId'      => 0,
            'teamId'     => 0,
        ]);
    }
}
