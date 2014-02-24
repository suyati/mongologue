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
            "emailId"=>"jdoe@x.com",
            "firstName"=>"John",
            "lastName"=>"Doe"
        );

        $app = new \Mongologue\Mongologue(new \MongoClient(), $dbName);
        $app->registerUser(
            new \Mongologue\User($user)
        );

        foreach ($app->getAllUsers() as $user) {
            $this->assertEquals("John Doe", $user->name());
            $this->assertEquals("jdoe@x.com", $user->email());
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
            "handle"=>"jack",
            "firstName"=>"Don",
            "lastName"=>"Bos"
        );
        $user2 = array(
            "id"=>"1238899884847",
            "handle"=>"roro",
            "firstName"=>"Zoro",
            "lastName"=>"Roro"
        );

        $app = new \Mongologue\Mongologue(new \MongoClient(), $dbName);
        $app->registerUser(
            new \Mongologue\User($user1)
        );
        $app->registerUser(
            new \Mongologue\User($user2)
        );

        $this->assertTrue($app->followUser($user2["id"], $user1["id"]));

        $followers = $app->getFollowers($user2["id"]);

        $following = $app->getFollowingUsers($user1["id"]);

        $this->assertTrue(
            in_array($user1["id"], $followers),
            'Follow not Registered at Followee'
        );

        $this->assertTrue(
            in_array($user2["id"], $following),
            'Follow not Registered at Follower'
        );
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
    /**
     * Shoud be able follow groups
     *
     * @test
     * 
     * @return void
     */
    public function shouldBeAbleToFollowGroups()
    {
        $dbName = self::DB_NAME;

        $user = array(
            "id"=>"1238899884878",
            "handle"=>"sam",
            "emailId" => "sam@pulp.fiction",
            "firstName"=>"Samuel",
            "lastName"=>"Jackson"
        );

        $group = array(
            "id" => 2,
            "name" => "Pulp Fiction"
        );

        $app = new \Mongologue\Mongologue(new \MongoClient(), $dbName);
        $app->registerUser(
            new \Mongologue\User($user)
        );
        $app->registerGroup(
            new \Mongologue\Group($group)
        );

        $this->assertTrue(
            $app->followGroup($group["id"], $user["id"]),
            "Group or User does not exist"
        );

        $followingGroups = $app->getFollowingGroups($user["id"]);

        $this->assertTrue(
            in_array($group["id"], $followingGroups),
            "Follow not registered at user"
        );

    }

}
?>