<?php

namespace App\Form;

use App\Entity\Session;
use App\Entity\Formation;
use App\Form\ProgramType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class SessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sessionName', TextType::class,
            [
                'attr' => [
                    'class' => 'form-control    '
                ]
            ])
            ->add('nbOfSpots', IntegerType::class,
            [
                'attr' => [
                    'class' => 'form-control    '
                ]
            ])
            ->add('startingDate', DateType::class,
            [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control    '
                ]
            ])
            ->add('finishingDate', DateType::class,
            [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control    '
                ]
            ])
            ->add('programs', CollectionType::class, [
                //la collection attends l'element qu'elle rentrera dans le form mais ce n'est pas obligatoire que ce soit un nouveau formulaire
                'entry_type' => ProgramType::class,
                'prototype' => true,
                //Autoriser l'ajout de nouveau element dans l'entité session qui seront persistés grace au cascade persist sur l'element program
                // Ca va activer un data prototype qui sera un attribut HTML qu'on pourra en javascript
                'allow_add' => true,
                'allow_delete' => true,
                //by_reference est obligatoire car Session n'a pas de setProgramm mais c'est Program qui contient setSession
                //Programm est propriétaire de la relation
                //Pour eviter un mappin false, on doit rajouter un by_reference => false
                'by_reference' => false,
            ])
            // ->add('students')
            // ->add('formation', EntityType::class,[
            //     'class' => Formation::class,
            //     'attr' => [
            //         'class' => 'form-control    '
            //     ]
            // ])
            ->add('Valider', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
        ]);
    }
}
