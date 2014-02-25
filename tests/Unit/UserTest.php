<?php
/**
 * File Containing Unit Tests for the User Class
 *
 * @category Mongologue
 * @package  Tests
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  NONE http://suyati.com
 * @version  0.1.1
 * @link     http://suyati.com
 */
namespace Mongologue\Tests;
use \Mongologue\Exceptions\User as UserExceptions;

/**
 * Class Containing Unit Tests for the User Class
 *
 * @category Mongologue
 * @package  Tests
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  NONE http://suyati.com
 * @version  0.1.1
 * @link     http://suyati.com
 */
class UnitTest extends \PHPUnit_Framework_TestCase
{
    const DB_NAME = "testUserDB";

    /**
     * Constructor of Class. Drops ALL TEST DBS
     */
    public function __construct()
    {
        parent::__construct();
        $client = new \MongoClient();
        $dbName = self::DB_NAME;
        $db = $client->$dbName;
        $db->drop();
        
    }

    /**
     * Check if Custom Exception is thrown
     *
     * @test
     * @expectedException \Mongologue\Exceptions\User\UserNotFoundException
     * 
     * @return void
     */
    public function shouldThrowExceptionForGettingUserWhichDoesNotExist()
    {
        $dbName = self::DB_NAME;

        $user = array(
            "id"=>"1238899884848",
            "handle"=>"jdoe",
            "emailId"=>"jdoe@x.com",
            "firstName"=>"John",
            "lastName"=>"Doe"
        );

        $app = new \Mongologue\Mongologue(new \MongoClient(), $dbName);
        $app->registerUser(
            new \Mongologue\User($user)
        );
        $app->getUser("1238899884849");
    }

    /**
     * Check if Users are Retrieved from Ids
     *
     * @test
     * 
     * @return void
     */
    public function shouldRetrieveUsersFromID()
    {
        $dbName = self::DB_NAME;

        $user = array(
            "id"=>"1238899884849",
            "handle"=>"jdoe",
            "emailId"=>"jdoe@x.com",
            "firstName"=>"Will",
            "lastName"=>"Smith"
        );

        $app = new \Mongologue\Mongologue(new \MongoClient(), $dbName);
        $app->registerUser(
            new \Mongologue\User($user)
        );
        $expected = $app->getUser("1238899884849")->name();

        $this->assertEquals($expected, "Will Smith", 'Names Are not Same for Returned users');
    }

    /**
     * Check if Custom Exception is thrown for duplicate User
     *
     * @test
     * @expectedException \Mongologue\Exceptions\User\DuplicateUserException
     * 
     * @return void
     */
    public function shouldThrowCustomExceptionIfADuplicateIdIsRegistered()
    {
        $dbName = self::DB_NAME;

        $user = array(
            "id"=>"1238899884850",
            "handle"=>"jgrisham",
            "emailId"=>"jgrisham@y.com",
            "firstName"=>"John",
            "lastName"=>"Grisham"
        );

        $user2 = array(
            "id"=>"1238899884850",
            "handle"=>"jgrisham",
            "emailId"=>"jgrisham@y.com",
            "firstName"=>"John",
            "lastName"=>"Grisham"
        );

        $app = new \Mongologue\Mongologue(new \MongoClient(), $dbName);
        $app->registerUser(
            new \Mongologue\User($user)
        );

        $app->registerUser(
            new \Mongologue\User($user2)
        );
        
    }


    public function shoulThrowCustomeExceptionIfAUserNotFollowing()
    {
        $dbName = self::DB_NAME;

        $request = array(
            "id"=>"1",
            "id"=>"2"
            );
        $app = new \Mongologue\Mongologue(new \MongoClient(), $dbName);
        $app->followUser(
            new \Mongologue\User($request)
            );
    }
}
?>