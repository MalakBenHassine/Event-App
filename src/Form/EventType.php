<?php

namespace App\Form;

use App\Entity\Events;
use App\Entity\Local;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'événement',
                'attr' => [
                    'placeholder' => 'Saisissez le nom de l\'événement',
                    'class' => 'form-control',
                ],
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de l\'événement',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])

            ->add('local', EntityType::class, [
                'class' => Local::class,
                'choice_label' => 'name',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Ajoutez une description de l\'événement',
                    'class' => 'form-control',
                    'rows' => 5,
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Créer l\'événement',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],

            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Events::class, // Associez le formulaire à l'entité Events
        ]);
    }
}

