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
namespace Mongologue\Tests\Unit;

use \Mongologue\Config;

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
class MongologueTest extends \PHPUnit_Framework_TestCase
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
    public function shouldSetupDatabaseAndCollectionsAnd()
    {
        $client = new \MongoClient();
        $dbName = self::DB_NAME;

        $collectionNames = array(
            "users",
            "posts",
            "comments",
            "groups"
        );

        $app = new \Mongologue\Mongologue(new \MongoClient(), $dbName);

        $collections = $client->selectDB($dbName)->getCollectionNames();
        foreach ($collectionNames as $key => $collection) {
            $this->assertTrue(in_array($collection, $collections));
        }
        

    }

    /**
     * Test if Users are Registered
     *
     * @test
     *
     * @return void
     */
    public function shouldBeAbleToRegisterUser()
    {
        $dbName = self::DB_NAME;

        $user = array(
            "id"=>"1238899884848",
            "handle"=>"jdoe",
            "firstName"=>"John",
            "lastName"=>"Doe"
        );

        $app = new \Mongologue\Mongologue(new \MongoClient(), $dbName);
        $app->registerUser(
            new \Mongologue\User($user)
        );

        foreach ($app->getAllUsers() as $user) {
            $this->assertEquals("John Doe", $user->name());
        }

    }
    /**
     * A USer can follow other Users
     *
     * @test
     * 
     * @return void
     */
    public function shouldBeAbleToFollowUSer()
    {
        $dbName = self::DB_NAME;

        $user1 = array(
            "id"=>"1238899884849",
            "handle"=>"nbos",
            "firstName"=>"Naveen",
            "lastName"=>"Bos"
        );
        $user2 = array(
            "id"=>"1238899884847",
            "handle"=>"unni",
            "firstName"=>"Unni",
            "lastName"=>"PN"
        );

        $app = new \Mongologue\Mongologue(new \MongoClient(), $dbName);
        $app->registerUser(
            new \Mongologue\User($user1)
        );
        $app->registerUser(
            new \Mongologue\User($user2)
        );

        $app->followUser($user2["id"], $user1["id"]);

        $followers = $app->getFollowers($user1["id"]);

        foreach ($followers as $id) {
            $this->assertEquals($user2["id"], $id);
        }
    }

    /**
     * Should Be Able To Register Group
     *
     * @test
     * 
     * @return [type] [description]
     */
    public function shouldBeAbleToRegisterGroup()
    {
        $dbName = self::DB_NAME;

        $group = array(
            "id" => 1,
            "name" => "Foo"
        );

        $app = new \Mongologue\Mongologue(new \MongoClient(), $dbName);
        $app->registerGroup(
            new \Mongologue\Group($group)
        );

        foreach ($app->getAllGroups() as $group) {
            $this->assertEquals("Foo", $group->name());
        }
    }

}
?>