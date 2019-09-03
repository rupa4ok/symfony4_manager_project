<?php

declare(strict_types=1);

namespace App\Model\User\Entity;

use App\Model\EntityNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class UserRepository
{
	private $em;
	/**
	 * @var EntityRepository
	 */
	private $repo;
	
	public function __construct(EntityManagerInterface $em)
	{
		$this->em = $em;
		$this->repo = $em->getRepository(User::class);
	}
	
	/**
	 * @param string $token
	 * @return User|null|object
	 */
	public function findByConfirmToken(string $token): ?User
	{
		return $this->repo->findOneBy(['confirmToken' => $token]);
	}
	
	/**
	 * @param string $token
	 * @return User|null|object
	 */
	public function findByResetToken(string $token): ?User
	{
		return $this->repo->findOneBy(['resetToken.token' => $token]);
	}
	
	public function get(Id $id): User
	{
		/**
		 * @var User $user
		 */
		if (!$user = $this->repo->find($id->getValue())) {
			throw new EntityNotFoundException('Пользователь не найден');
		}
		return $user;
	}
	
	public function getByEmail(Email $email): User
	{
		/**
		 * @var User $user
		 */
		if (!$user = $this->repo->findOneBy(['email' => $email->getValue()])) {
			throw new EntityNotFoundException('Пользователь не найден');
		}
		return $user;
	}
	
	public function hasByEmail(Email $email): bool
	{
		return $this->repo->createQueryBuilder('t')
			->select('COUNT(t.id)')
			->andWhere('t.email = :email')
			->setParameter(':email', $email->getValue())
			->getQuery()
			->getSingleResult() > 0;
	}
	
	public function hasByNetworkIdentity(string $network, string $identity): bool
	{
		return $this->repo->createQueryBuilder('t')
			->select('COUNT(t.id)')
			->innerJoin('t.networks', 'n')
			->andWhere('n.networks = :networks and n.identity = :identity')
			->setParameter(':network', $network)
			->setParameter(':identity', $identity)
			->getQuery()
			->getSingleResult() > 0;
	}

    public function add(User $user): void
    {
    	$this->em->persist($user);
    }
    
}