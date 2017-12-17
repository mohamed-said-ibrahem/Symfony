<?php

namespace WorkBundle\Repository;
use Doctrine\ORM\EntityRepository;
use WorkBundle\Entity\User;
/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends \Doctrine\ORM\EntityRepository
{

    public function findOneByName($input)
    {
        $user = $this->createQueryBuilder('a')
                ->select()
                ->where('a.username = :username')
                ->setParameter('username', '%' . $input . '%')
                ->getQuery()
                ->getResult();

        return $user;
    }
}
