<?php
namespace Puzzle\Admin\ContactBundle\Form\Type;

use Puzzle\Admin\ContactBundle\Form\Model\AbstractContactType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 */
class ContactCreateType extends AbstractContactType
{
	public function configureOptions(OptionsResolver $resolver) {
		parent::configureOptions($resolver);
		
		$resolver->setDefault('csrf_token_id', 'contact_create');
		$resolver->setDefault('validation_groups', ['Create']);
	}
	
	public function getBlockPrefix() {
		return 'admin_contact_create';
	}
}
