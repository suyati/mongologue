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
            "groups",
            "inbox"
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

        $group = array(
            "id" => 12,
            "name" => "Grand Slam"
        );


        $app = new \Mongologue\Mongologue(new \MongoClient(), $dbName);
        
        $app->registerGroup(
            new \Mongologue\Group($group)
        );

        $app->registerUser(
            new \Mongologue\User($user),
            array($group["id"])
        );

        $members = $app->getGroupMembers($group["id"]);

        $this->assertContains($user["id"], $members);

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

        $groupNames = array();
        foreach ($app->getAllGroups() as $group) {
            $groupNames[] = $group->name();
        }
        $this->assertContains("Foo", $groupNames);
        
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

        $followers = $app->getGroupFollowers($group["id"]);
        $this->assertContains($user["id"], $followers);

    }

    /**
     * Post should be Created
     *
     * @test
     * 
     * @return void
     */
    public function shouldBeAbleToMakePostsAndComments()
    {
        $dbName = self::DB_NAME;

        $user = array(
            "id"=>"1238899884579",
            "handle"=>"rudolph",
            "emailId" => "rudy@pulp.fiction",
            "firstName"=>"Rudolph",
            "lastName"=>"RedNose"
        );

        $app = new \Mongologue\Mongologue(new \MongoClient(), $dbName);
        $app->registerUser(
            new \Mongologue\User($user)
        );

        $post = array(
            "userId"=>"1238899884579",
            "datetime"=>"12.01.2014",
            "content"=>"hello testing",
            "filesToBeAdded" => array(
                "tests/resources/sherlock.jpg"=>array(
                    "type"=>"jpeg",
                    "size"=>"100"
                )
            )
        );

        $postId = $app->createPost($post);

        $res = $app->getPost($postId);
        
        foreach ($res->getFiles() as $key => $id) {
            $file = $app->getFile($id);
            $this->assertEquals("tests/resources/sherlock.jpg", $file->getFileName());
        }

        $this->assertEquals($post["content"], $res->getContent());

    }

    /**
     * Check for Messaging
     *
     * @test
     * 
     * @return void
     */
    public function shouldWriteMessagesIntoInboxQueue()
    {
        $dbName = self::DB_NAME;

        $user1 = array(
            "id"=>"1238899884791",
            "handle"=>"jdoe_1",
            "emailId"=>"jdoe1@x.com",
            "firstName"=>"John_1",
            "lastName"=>"Doe"
        );
        $user2 = array(
            "id"=>"1238899884792",
            "handle"=>"jdoe_2",
            "emailId"=>"jdoe2@x.com",
            "firstName"=>"John_2",
            "lastName"=>"Doe"
        );
        $user3 = array(
            "id"=>"1238899884793",
            "handle"=>"jdoe_3",
            "emailId"=>"jdoe3@x.com",
            "firstName"=>"John_3",
            "lastName"=>"Doe"
        );
        $user4= array(
            "id"=>"1238899884794",
            "handle"=>"jdoe_4",
            "emailId"=>"jdoe2@x.com",
            "firstName"=>"John_4",
            "lastName"=>"Doe"
        );

        $group1 = array(
            "id" => 4,
            "name" => "Cool Group 1"
        );

        $group2 = array(
            "id" => 5,
            "name" => "Cool Group 2"
        );

        $group3 = array(
            "id" => 6,
            "name" => "Cool Group 3"
        );


        $app = new \Mongologue\Mongologue(new \MongoClient(), $dbName);
        
        $app->registerGroup(
            new \Mongologue\Group($group1)
        );
        $app->registerGroup(
            new \Mongologue\Group($group2)
        );
        $app->registerGroup(
            new \Mongologue\Group($group3)
        );

        $app->registerUser(
            new \Mongologue\User($user1),
            array($group1["id"])
        );

        $app->registerUser(
            new \Mongologue\User($user2),
            array($group1["id"])
        );

        $app->registerUser(
            new \Mongologue\User($user3),
            array($group2["id"])
        );

        $app->registerUser(
            new \Mongologue\User($user4),
            array($group3["id"])
        );

        $app->followUser($user3["id"], $user1["id"]);
        $app->followUser($user2["id"], $user1["id"]);

        $app->followUser($user1["id"], $user3["id"]);

        $app->followGroup($group3["id"], $user2["id"]);
        $app->followGroup($group1["id"], $user3["id"]);
        $app->followGroup($group1["id"], $user4["id"]);

        $post1 = array(
            "userId"=>$user1["id"],
            "datetime"=>"12.01.2014",
            "content"=>"user one",
            "filesToBeAdded" => array(
                "tests/resources/sherlock.jpg"=>array(
                    "type"=>"jpeg",
                    "size"=>"100"
                )
            )
        );

        $post2 = array(
            "userId"=>$user4["id"],
            "datetime"=>"12.01.2014",
            "content"=>"user four",
        );

        $postId1 = $app->createPost($post1);
        $postId2 = $app->createPost($post2);

        $messages = array("user one", "user four");

        $feed = $app->getFeed($user2["id"]);

        foreach ($feed as $key => $post) {
            $this->assertEquals($user2["id"], $post["recipient"]);
            $this->assertContains($post["content"], $messages);
        }

    }

}
?>