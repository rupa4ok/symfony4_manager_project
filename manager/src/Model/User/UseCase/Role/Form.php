<?php

namespace App\Model\User\UseCase\Role;

use App\Model\User\Entity\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
	
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('role', Type\ChoiceType::class, ['choices' => [
				'User' => Role::USER,
				'Admin' => Role::ADMIN
			]]);
	}
	
	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => Command::class,
		]);
	}
}