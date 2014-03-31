<?php
/**
 * File Containing Unit Tests for the Mongologue Class
 *
 * @category Mongologue
 * @package  Tests
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  NONE http://suyati.com
 * @version  0.1.1
 * @link     http://suyati.com
 */
namespace Mongologue\Tests\Integration;

/**
 * Class Testing Mongologue 
 *
 * @category Quack
 * @package  Tests
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  NONE http://suyati.com
 * @version  0.1.1
 * @link     http://suyati.com
 */
class MongologueSpecs extends \PHPUnit_Framework_TestCase
{
    const DB_NAME = "testTDB";

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
     * should Setup Database And Collections
     *
     * @test
     * 
     * @return void
     */
    public function shouldSetupDatabaseAndCollections()
    {
        $client = new \MongoClient();
        $dbName = self::DB_NAME;

        $collectionNames = array(
            "users",
            "groups"
        );

        $factory = new \Mongologue\Factory();
        $app = $factory->createMongologue(new \MongoClient(), $dbName);

        $collections = $client->selectDB($dbName)->getCollectionNames();
        foreach ($collectionNames as $key => $collection) {
            $this->assertTrue(in_array($collection, $collections));
        }
    }

    /**
     * Test if Users can be registered
     *
     * @test
     * 
     * @return void
     */
    public function shouldRegisterUser()
    {
        $client = new \MongoClient();
        $dbName = self::DB_NAME;

        $factory = new \Mongologue\Factory();
        $app = $factory->createMongologue(new \MongoClient(), $dbName);
        
        $userData = array(
            "id" => 40,
            "handle" => "tommy",
            "firstName" => "Tommy",
            "lastName" => "Jones"
        );

        $expectedUsersList = array(
            new \Mongologue\Models\User(
                array(
                    "id" => 40,
                    "handle" => "tommy",
                    "firstName" => "Tommy",
                    "lastName" => "Jones",
                    "email" => null,
                    "pic" => null,
                    "following" => array(),
                    "followers" => array(),
                    "groups" => array(),
                    "blocking" => array(),
                    "followingGroups" => array(),
                    "likes" => array(),
                    "data" => array()
                )   
            )
        );

        $app->user('register', new \Mongologue\Models\User($userData));

        $this->assertEquals($expectedUsersList, $app->user('all'));
    }

}