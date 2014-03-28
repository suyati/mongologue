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

// require_once 'User.php';

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
     * @expectedException Mongologue\Exceptions\User\AlreadyFollowingException
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

        $this->assertTrue($app->unFollowUser($user2["id"], $user1["id"]));
        $this->assertTrue($app->followUser($user2["id"], $user1["id"]));
        $follow = $app->followUser($user2["id"], $user1["id"]);

        
    }

    /**
     * A User cannot unFollow Users when they have not following
     *
     * @test
     * @expectedException Mongologue\Exceptions\User\NotFollowingException
     * @return void
     */
    public function shouldBeAbleToUnFollowUSer()
    {
        $dbName = self::DB_NAME;

        $user1 = array(
            "id"=>"1238899884845",
            "handle"=>"jack1",
            "firstName"=>"Don1",
            "lastName"=>"Bos1"
        );
        $user2 = array(
            "id"=>"1238899884846",
            "handle"=>"roro1",
            "firstName"=>"Zoro1",
            "lastName"=>"Roro1"
        );

        $app = new \Mongologue\Mongologue(new \MongoClient(), $dbName);
        $app->registerUser(
            new \Mongologue\User($user1)
        );
        $app->registerUser(
            new \Mongologue\User($user2)
        );
        //user
        $this->assertTrue($app->unFollowUser($user2["id"], $user1["id"]));
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
        $savedGroup = $app->getGroup($group["id"]);
        
        $this->assertEquals("Foo", $savedGroup->name());
        
    }

    /**
     * Testing Sub Group Formation and Retrieval of Id from Name and Parent Id
     *
     * @test
     * 
     * @return void
     */
    public function shouldbeAbletoRetrieveIdFromGroupNameAndParentID()
    {
        $dbName = self::DB_NAME;

        $group_1 = array(
            "id" => 21,
            "name" => "Foo21"
        );

        $group_2 = array(
            "id" => 22,
            "name" => "Foo21"
        );

        $subgroup_1 = array(
            "id" => 23,
            "name" => "1",
            "parent" => 21
        );

        $subgroup_2 = array(
            "id" => 24,
            "name" => "1",
            "parent" => 22
        );

        $app = new \Mongologue\Mongologue(new \MongoClient(), $dbName);

        $app->registerGroup(
            new \Mongologue\Group($group_1)
        );

        $app->registerGroup(
            new \Mongologue\Group($group_2)
        );

        $app->registerGroup(
            new \Mongologue\Group($subgroup_1)
        );

        $app->registerGroup(
            new \Mongologue\Group($subgroup_2)
        );

        $subgroup_one = $app->getGroup($subgroup_1["id"]);

        $this->assertEquals($group_1["id"], $subgroup_one->parent());

        $subgroup_two = $app->getGroup($subgroup_2["id"]);

        $this->assertEquals($group_2["id"], $subgroup_two->parent());

        $this->assertEquals($subgroup_1["id"], $app->getGroupIdFromName($subgroup_1["name"], $group_1["id"]));
        $this->assertEquals($group_1["id"], $app->getGroupIdFromName($group_1["name"]));
    }

    /**
     * Should Be Able To Register Category
     *
     * @test
     * 
     * @return [type] [description]
     */
    public function shouldBeAbleToRegisterCategory()
    {
        $dbName = self::DB_NAME;

        $category = array(
            "id" => 1,
            "name" => "New Category"
        );

        $app = new \Mongologue\Mongologue(new \MongoClient(), $dbName);
        $app->registerCategory(
            new \Mongologue\Category($category)
        );

        $categoryNames = array();
        foreach ($app->getAllCategories() as $category) {
            $categoryNames[] = $category->name();
        }
        $this->assertContains("New Category", $categoryNames);
        
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
        $this->assertTrue($app->unFollowGroup($group["id"], $user["id"]));

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
        $user2 = array(
            "id"=>"1238899884580",
            "handle"=>"Rijo",
            "emailId" => "rijo@pulp.fiction",
            "firstName"=>"Rijo",
            "lastName"=>"Rijos"
        );
        $app = new \Mongologue\Mongologue(new \MongoClient(), $dbName);
        $app->registerUser(
            new \Mongologue\User($user)
        );
        $app->registerUser(
            new \Mongologue\User($user2)
        );
        $post = array(
            "userId"=>"1238899884579",
            "datetime"=>"12.01.2014",
            "content"=>"hello testing",
            "filesToBeAdded" => array(
                __DIR__."/resources/sherlock.jpg"=>array(
                    "type"=>"jpeg",
                    "size"=>"100"
                )
            )
        );

        $postId = $app->createPost($post);

        $res = $app->getPost($postId);
        
        foreach ($res->getFiles() as $key => $id) {
            $file = $app->getFile($id);
            $this->assertEquals(__DIR__."/resources/sherlock.jpg", $file->getFileName());
        }

        $this->assertEquals($post["content"], $res->getContent());

        $comment = array(
            "parent"=>$postId,
            "userId"=>"1238899884580",
            "datetime"=>"12.01.2014",
            "content"=>"hello comment testing",
            'postType' => "comment",
            "filesToBeAdded" => array(
                __DIR__."/resources/sherlock.jpg"=>array(
                    "type"=>"jpeg",
                    "size"=>"100"
                )
            )
            );
        $postId = $app->createComment($comment);
        $res = $app->getPost($postId);
        foreach ($res->getFiles() as $key => $id) {
            $file = $app->getFile($id);
            $this->assertEquals(__DIR__."/resources/sherlock.jpg", $file->getFileName());
        }
        $this->assertEquals($comment["content"], $res->getContent());
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
                __DIR__."/resources/sherlock.jpg"=>array(
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

        $post3 = array(
            "userId"=>$user2["id"],
            "datetime"=>"14.03.2014",
            "content"=>"user two",
            "filesToBeAdded" => array(
                __DIR__."/resources/sherlock.jpg"=>array(
                    "type"=>"jpeg",
                    "size"=>"100"
                )
            )
        );

        $postId1 = $app->createPost($post1);
        $postId2 = $app->createPost($post2);
        $postId3 = $app->createPost($post3);

        $messages = array("user one", "user four");

        $res = $app->getPost($postId2);
        $this->assertEquals($post2["content"], $res->getContent());

        $feed = $app->getFeed($user2["id"]);

        foreach ($feed as $key => $post) {
            $this->assertEquals($user2["id"], $post["recipient"]);
            $this->assertContains($post["content"], $messages);
        }

    }

    /**
     * Should Be Able To Add PremadePosts
     *
     * @test
     * 
     * @return [type] [description]
     */
    public function shouldBeAbleToAddPremadePosts()
    {
        $dbName = self::DB_NAME;

        $premadepost = array(
            "id" => 1,
            "name" => "New Premade Post"
        );

        $app = new \Mongologue\Mongologue(new \MongoClient(), $dbName);
        $app->registerPremadepost(
            new \Mongologue\Premadepost($premadepost)
        );

        $premadePosts = array();
        foreach ($app->getAllPremadepost() as $premadepost) {
            $premadePosts[] = $premadepost->name();
        }
        $this->assertContains("New Premade Post", $premadePosts);
        
    }

     /**
     * Should Be Able To throw Post not found exception
     *
     * @test
     *
     * @expectedException Mongologue\Exceptions\Post\PostNotFoundException
     * @return [type] [description]
     */
    public function shouldBeThrowpPostNotFoundException()
    {
        $dbName = self::DB_NAME;
        $user = array(
            "id"=>"1238899884581",
            "handle"=>"rudolph",
            "emailId" => "rudy@pulp.fiction",
            "firstName"=>"Rudolph",
            "lastName"=>"RedNose"
        );
        $user2 = array(
            "id"=>"1238899884582",
            "handle"=>"Rijo",
            "emailId" => "rijo@pulp.fiction",
            "firstName"=>"Rijo",
            "lastName"=>"Rijos"
        );
        $app = new \Mongologue\Mongologue(new \MongoClient(), $dbName);
        $app->registerUser(
            new \Mongologue\User($user)
        );
        $app->registerUser(
            new \Mongologue\User($user2)
        );
        $post = array(
            "userId"=>"1238899884579",
            "datetime"=>"12.01.2014",
            "content"=>"hello testing",
            "filesToBeAdded" => array(
                __DIR__."/resources/sherlock.jpg"=>array(
                    "type"=>"jpeg",
                    "size"=>"100"
                )
            )
        );

        $postId = $app->createPost($post);

        $res = $app->getPost($postId);
        
        foreach ($res->getFiles() as $key => $id) {
            $file = $app->getFile($id);
            $this->assertEquals(__DIR__."/resources/sherlock.jpg", $file->getFileName());
        }

        $this->assertEquals($post["content"], $res->getContent());
        $res = $app->getPost("1234");
    }


    /**
     * Should Be Able To Likepost
     *
     * @test
     *
     * @expectedException Mongologue\Exceptions\Post\AlreadyLikesThisPostException
     * @return [type] [description]
     */
    public function shouldBeAbleToLikePosts()
    {
        $dbName = self::DB_NAME;

        $user1 = array(
            "id"=>"123456789",
            "handle"=>"jdoe_1",
            "emailId"=>"jdoe1@x.com",
            "firstName"=>"John_1",
            "lastName"=>"Doe"
        );

        $user2 = array(
            "id"=>"1238899884782",
            "handle"=>"jdoe_2",
            "emailId"=>"jdoe2@x.com",
            "firstName"=>"John_2",
            "lastName"=>"Doe"
        );

        $post1 = array(
            "userId"=>"123456789",
            "datetime"=>"14.03.2014",
            "content"=>"user one's post",
            "filesToBeAdded" => array(
                __DIR__."/resources/sherlock.jpg"=>array(
                    "type"=>"jpeg",
                    "size"=>"100"
                )
            )
        );

        $app = new \Mongologue\Mongologue(new \MongoClient(), $dbName);
        $app->registerUser(
            new \Mongologue\User($user1)
        );
        $app->registerUser(
            new \Mongologue\User($user2)
        );
        $postId1 = $app->createPost($post1);
        $res = $app->getPost($postId1);
        $this->assertEquals($post1["content"], $res->getContent());
        
        $this->assertTrue($app->likePost($postId1, $user2["id"]));

        $user = \Mongologue\User::fromID($user2["id"], $app->userCollection());
        
        $this->assertContains($postId1, $user->getPostLikes());

        $post = \Mongologue\Post::fromID($postId1, $app->postCollection());
 
        $this->assertContains($user2["id"], $post->likedUsers());

        $this->assertTrue($app->likePost($postId1, $user2["id"]));
    }
}
?>