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
class MongologueSpec extends \PHPUnit_Framework_TestCase
{
    protected static $mongologue;

    const DB_NAME = "testTDB";

    /**
     * setUpBeforeClass
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        $factory = new \Mongologue\Factory();
        self::$mongologue = $factory->createMongologue(new \MongoClient(), self::DB_NAME);  
    }

    /**
     * tearDownAfterClass
     * 
     * @return void
     */
    public static function tearDownAfterClass()
    {
        $client = new \MongoClient();
        $dbName = self::DB_NAME;
        $db = $client->$dbName;
        $db->drop();

        self::$mongologue = null;
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

        self::$mongologue->user('register', new \Mongologue\Models\User($userData));

        $this->assertEquals($expectedUsersList, self::$mongologue->user('all'));
    }

    /**
     * should Register Group And Retrieve By Query And Id
     * 
     * @param array $groupData        GroupData
     * @param array $expectedDocument Expected Document
     * @param array $query            Query to find Group
     *
     * @test
     * 
     * @dataProvider providerForValidGroupData
     * @return void
     */
    public function shouldRegisterGroupAndRetrieveByQueryAndId($groupData, $expectedDocument, $query)
    {
        self::$mongologue->group('register', new \Mongologue\Models\Group($groupData));

        $this->assertEquals($expectedDocument, self::$mongologue->group('find', $groupData["id"]));
        $this->assertEquals($expectedDocument, self::$mongologue->group('find', $query));
    }

    /**
     * Provide Valid Group Data
     * 
     * @return array List of Valid Group Data
     */
    public function providerForValidGroupData()
    {
        return array(
            array(
                array("id"=>1, "name" => "Scientists"),
                array(
                    "id"=>1, 
                    "name" => "Scientists", 
                    "members" => array(), 
                    "followers" => array(),
                    "parent" => null,
                    "type" => null,
                    "data" => array()
                ),
                array("name"=>"Scientists")
            ),
            array(
                array("id"=>4, "name"=>"Botanist"),
                array(
                    "id"=>4, 
                    "name" => "Botanist", 
                    "members" => array(), 
                    "followers" => array(),
                    "parent" => null,
                    "type" => null,
                    "data" => array()
                ),
                array("name"=>"Botanist")
            ),
            array(
                array("id" => 2, "name" => "Physicist", "parent"=>1),
                array(
                    "id"=>2, 
                    "name" => "Physicist", 
                    "members" => array(), 
                    "followers" => array(),
                    "parent" => 1,
                    "type" => null,
                    "data" => array()
                ),
                array("name"=>"Physicist", "parent"=>1)
            ),
            array(
                array("id" => 3, "name"=> "Botanist", "parent"=>1),
                array(
                    "id"=>3, 
                    "name" => "Botanist", 
                    "members" => array(), 
                    "followers" => array(),
                    "parent" => 1,
                    "type" => null,
                    "data" => array()
                ),
                array("name"=>"Botanist", "parent"=>1)
            )
        );
    }

}