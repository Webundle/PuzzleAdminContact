<?php
namespace Puzzle\Admin\ContactBundle\Form\Type;

use Puzzle\Admin\ContactBundle\Form\Model\AbstractContactType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 */
class ContactUpdateType extends AbstractContactType
{
// 	public function __construct() {
// 		parent::__construct(false);
// 	}
	
	public function configureOptions(OptionsResolver $resolver) {
		parent::configureOptions($resolver);
		
		$resolver->setDefault('csrf_token_id', 'contact_update');
		$resolver->setDefault('validation_groups', ['Update']);
	}
	
	public function getBlockPrefix() {
		return 'admin_contact_update';
	}
}
