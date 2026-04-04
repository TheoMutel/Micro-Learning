<?php

namespace App\Form;

use App\Entity\Tutorial;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class TutorialType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre du tutoriel',
                'attr' => [
                    'placeholder' => 'Ex: Débuter avec Symfony',
                    'class' => 'w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Décrivez votre tutoriel...',
                    'class' => 'w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
                ],
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Backend' => 'backend',
                    'Frontend' => 'frontend',
                    'DevOps' => 'devops',
                    'Bases de données' => 'database',
                    'Autre' => 'other',
                ],
                'attr' => [
                    'class' => 'w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500',
                ],
            ])
            ->add('steps', CollectionType::class, [
                'entry_type' => StepType::class,
                'label' => 'Étapes du tutoriel',
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'attr' => ['class' => 'steps-collection'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer le tutoriel',
                'attr' => [
                    'class' => 'bg-indigo-600 text-white px-6 py-3 rounded-md hover:bg-indigo-700 font-medium',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tutorial::class,
        ]);
    }
}
