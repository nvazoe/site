<?php

namespace App\DataFixtures;


use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        
        foreach ($this->getUserData() as [$username, $password, $email, $fullname, $role]) {
            $user = new User();
            $user->setUsername($username);
            $user->setFirstname($fullname);
            $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
            $user->setEmail($email);
            $user->setRoles($role);
            
//            $group = new Group();
//            $group->setName('Nom du Role '.$role[0]);
//            $group->setRoles($role);
//           
//            $user->addGroup($group);
            
            $manager->persist($user);
//            $manager->persist($group);
//            $this->addReference($username, $user);
        }
        
        $manager->flush();
    }
    
    private function getUserData(): array
    {
        return [
            ['benjamin', 'decanet31', 'benjamin@decanet.fr', 'Benjamin ARGOUD', ['ROLE_ADMIN']],
            ['john_user', 'kitten', 'john_user@symfony.com', 'John DOE', ['ROLE_USER']],
        ];
    }
}
