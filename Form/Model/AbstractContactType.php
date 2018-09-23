<?php

namespace Puzzle\Admin\ContactBundle\Form\Model;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 */
class AbstractContactType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('civility', ChoiceType::class, array(
                'translation_domain' => 'admin',
                'label' => 'contact.civility.base',
                'label_attr' => ['class' => 'form-label'],
                'choices' => [
                    'contact.civility.default.empty' => '',
                    'contact.civility.default.M' => 'contact.civility.default.M',
                    'contact.civility.default.Mrs' => 'contact.civility.default.Mrs',
                    'contact.civility.default.Ms' => 'contact.civility.default.Ms'
                ],
                'attr' => ['class' => 'form-control select'],
                'required' => false
            ))
            ->add('firstName', TextType::class, [
                'translation_domain' => 'admin',
                'label' => 'contact.firstName',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
                'required' => false
            ])
            ->add('lastName', TextType::class, [
                'translation_domain' => 'admin',
                'label' => 'contact.lastName',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
                'required' => false
            ])
            ->add('email', EmailType::class, [
                'translation_domain' => 'admin',
                'label' => 'contact.email',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('phone', TextType::class, [
                'translation_domain' => 'admin',
                'label' => 'contact.phone',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('location', TextType::class, [
                'translation_domain' => 'admin',
                'label' => 'contact.location',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
                'required' => false
            ])
            ->add('company', TextType::class, [
                'translation_domain' => 'admin',
                'label' => 'contact.company',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
                'required' => false
            ])
            ->add('position', TextType::class, [
                'translation_domain' => 'admin',
                'label' => 'contact.position',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
                'required' => false
            ])
        ;
    }
}
