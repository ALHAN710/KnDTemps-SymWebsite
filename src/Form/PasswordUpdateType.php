<?php

namespace App\Form;

use App\Form\ApplicationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class PasswordUpdateType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('newPassword', PasswordType::class, $this->getConfiguration("Nouveau Mot de Passe", "Entrer votre nouveau mot de passe svp ..."))
            ->add('confirmPassword', PasswordType::class, $this->getConfiguration("Confirmation Mot de Passe", "Confirmer votre nouveau mot de passe svp ..."));

        if ($options['isUser']) {
            $builder
                ->add('oldPassword', PasswordType::class, $this->getConfiguration("Mot de Passe Actuel", "Entrer votre Mot de Passe courant..."));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
            'isUser'   => true,
        ]);
    }
}
