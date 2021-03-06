<?php
/**
 * Created by PhpStorm.
 * User: Patrick
 * Date: 06.11.2016
 * Time: 22:29
 */

namespace AppBundle\Test;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EnhancedWebTestCase extends WebTestCase
{

    public function setUp()
    {
        self::bootKernel();
    }

    private $asserter;

    protected function createAuthorizedClient($username, $password)
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => $username,
            'PHP_AUTH_PW'   => $password
        ));

        return $client;
    }


    // singleton
    protected function asserter()
    {
        if($this->asserter == null)
        {
            $this->asserter = new ResponseAsserter();
        }
        return $this->asserter;
    }

    protected function createUser($username, $plainPassword = 'Test1234')
    {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($username.'@test.com');
        $password = $this->getService('security.password_encoder')
            ->encodePassword($user, $plainPassword);
        $user->setPassword($password);

        $em = $this->getEm();
        $em->persist($user);
        $em->flush();

        return $user;
    }

    protected function getAuthorizedHeaders($username, $headers = array())
    {
        $token = $this->getService('lexik_jwt_authentication.encoder')
            ->encode(['username' => $username]);
        $headers['HTTP_Authorization'] = 'Bearer '.$token;
        return $headers;
    }

    protected function getService($id)
    {
        return self::$kernel->getContainer()
            ->get($id);
    }

    /**
     * @return EntityManager
     */
    protected function getEm()
    {
        return $this->getService('doctrine.orm.entity_manager');
    }
}