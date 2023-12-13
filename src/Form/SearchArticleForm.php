<?php

namespace App\Form;

use App\Data\SearchDataArticle;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchArticleForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('q', TextType::class, [
                'label' => 'Rechercher par mots clés',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Rechercher',
                ]
            ])
            ->add('category', EntityType::class, [
                'label' => 'Catégorie',
                'required' => false,
                'class' => Category::class,
                'placeholder' => 'Toutes les catégories',
                'choice_label' => 'label',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Rechercher',
                'attr' => [
                    'class' => 'btn btn-primary' // You can customize the button styling here
                ],
            ]);;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchDataArticle::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }
}
