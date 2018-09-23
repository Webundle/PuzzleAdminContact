<?php

namespace Puzzle\Admin\ContactBundle\Form\Model;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 */
class AbstractGroupType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'translation_domain' => 'admin',
                'label' => 'contact.group.name',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('parent', HiddenType::class)
            ->add('description', TextType::class, [
                'translation_domain' => 'admin',
                'label' => 'contact.group.description',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
                'required' => false
            ])
            ->add('save', SubmitType::class, array(
                'translation_domain' => 'admin',
                'label' => 'button.save',
                'attr' => [
                    'class' => "btn btn-primary"
                ]
            ))
        ;
    }
}
