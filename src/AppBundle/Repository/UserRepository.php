<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

/**
 * Class UserRepository
 * @package AppBundle\Repository
 */
class UserRepository extends EntityRepository
{
    /**
     * @param $email
     * @return mixed
     */
    public function getUserByEmail($email)
    {
        return $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param User $user
     * @param string $faceBookId
     */
    public function updateUserByFacebookId($user, $faceBookId)
    {
        $user->setFbId($faceBookId);
        $em = $this->getEntityManager();
        $em->persist($user);
        return $em->flush();
    }
}
