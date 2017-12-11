<?php

namespace WorkBundle\Repository;
use Doctrine\ORM\EntityRepository;
use WorkBundle\Entity\Employee;
/**
 * EmployeeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EmployeeRepository extends \Doctrine\ORM\EntityRepository
{
    public function createEmployee($application,$nameArabic,$nameEnglish,$birthDate,
    $address,$homePhone,$mobilePhone,$emergencyContactPerson,$emergencyPersonNumber,
    $emailPersonal,$idCardNumber,$academicDegree,$graduationDate,$joiningDate)
    {
        $employee = new Employee();
        $employee->setApplication($application)->setNameArabic($nameArabic)
                 ->setNameEnglish($nameEnglish)->setBirthDate($birthDate)
                 ->setAddress($address)->setHomePhone($homePhone)
                 ->setMobilePhone($mobilePhone)->setEmergencyContactPerson($emergencyContactPerson)
                 ->setEmergencyPersonNumber($emergencyPersonNumber)->setEmailPersonal($emailPersonal)
                 ->setIdCardNumber($idCardNumber)->setAcademicDegree($academicDegree)
                 ->setGraduationDate($graduationDate)->setJoiningDate($joiningDate);
        
        $em = $this->getEntityManager();
        $em->persist($employee);
        $em->flush();

        return $employee;
    }

    public function findByName($employeeName)
    {
        $employees = $this->createQueryBuilder('a')
                     ->select()
                     ->where('a.nameArabic LIKE :username')
                     ->orWhere('a.nameEnglish LIKE :username')
                     ->setParameter('username', '%' . $employeeName . '%')
                     ->orderBy('a.id' , 'ASC')
                     ->getQuery()
                     ->getResult();
        return $employees;
    }

    public function findEmployees()
    {
        $employess = $this->createQueryBuilder('b')
                    ->select()
                    ->getQuery()
                    ->getResult();
        return $employees;
    }

    public function findByPhone($number)
    {
        $employee = $this->createQueryBuilder('c')
                 ->select()
                 ->where('c.mobilePhone = :phone')
                 ->orWhere('c.homePhone = :phone')
                 ->setParameter('phone',$number)
                 ->getQuery()
                 ->getResult();
        return $employee;
    }

    public function findByPosition($position)
    {
        $employees = $this->createQueryBuilder('d')
                     ->select()
                     ->where('d.currentPosition LIKE :position')
                     ->setParameter('position', '%' . $position . '%')
                     ->getQuery()
                     ->getResult();
        return $employees;
    }

    public function deleteEmployee($employeeId)
    { 
        $this->createQueryBuilder('e')
             ->delete()
             ->where('e.id = :id')
             ->setParameter('id',$employeeId)
             ->getQuery()
             ->execute();
    }

    public function findByEmail($email)
    {
        $employees = $this->createQueryBuilder('f')
                      ->select()
                      ->where('f.emailPersonal LIKE :email')
                      ->setParameter('email', '%' . $email . '%')
                      ->orderBy('f.id', 'ASC')
                      ->getQuery()
                      ->getResult();
        return $employees;
    }
}
