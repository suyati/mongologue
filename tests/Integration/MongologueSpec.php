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
        self::$mongologue = $factory->createMongologue(new \MongoClient("mongodb://127.0.0.1"), self::DB_NAME);
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
            "groups",
            "inbox",
            "category",
            "counters"
        );

        $collections = $client->selectDB($dbName)->getCollectionNames();
        foreach ($collectionNames as $key => $collection) {
            $this->assertTrue(in_array($collection, $collections));
        }
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
        $id = self::$mongologue->group('register', new \Mongologue\Models\Group($groupData));
        $this->assertEquals($expectedDocument, self::$mongologue->group('find', $id));
        $this->assertEquals($expectedDocument, self::$mongologue->group('find', $query));
    }

    /**
     * Test if Users can be registered
     *
     * @test
     *
     * @depends shouldRegisterGroupAndRetrieveByQueryAndId
     * @dataProvider provideValidUserData
     * @return void
     */
    public function shouldRegisterUser($userData, $expectedDocument)
    {
        self::$mongologue->user('register', new \Mongologue\Models\User($userData));
        $this->assertContains($expectedDocument, self::$mongologue->user('all'));

        foreach ($expectedDocument["groups"] as $groupId) {
            $group = self::$mongologue->group('find', $groupId);
            $this->assertContains($expectedDocument["id"], $group["members"]);
        }
    }

    /**
     * Should Create and Retrieve Posts
     * 
     * @param array $postData Post Data
     *
     * @test
     *
     * @dataProvider providerValidPostData
     * @return void
     */
    public function shouldCreatePostAndRetrieveById($postData)
    {
        $id = self::$mongologue->post('create', new \Mongologue\Models\Post($postData));
        $retrievedPost = self::$mongologue->post('find', $id);
        $this->assertEquals($postData["content"], $retrievedPost["content"]);
    }

    /**
     * Should like Posts
     *
     * @param array $postData Post Data
     *  
     * @test
     * 
     * @dataProvider providerValidPostData
     * @return void
     */
    public function shouldBeAbleToLikePosts($postData)
    {
        $userId = 40;
        $id = self::$mongologue->post('create', new \Mongologue\Models\Post($postData));
        self::$mongologue->post('like', $id, $userId);
        $retrievedPost = self::$mongologue->post('find', $id);
        $this->assertEquals(array($userId), $retrievedPost["likes"]);

        $retrievedUser = self::$mongologue->user('find', $userId);
        $this->assertContains($id, $retrievedUser["likes"]);
    }
    
    /**
     * Should get Post comments
     * 
     * @param array $commentData comment Data
     * 
     * @test
     *
     * @dataProvider providerValidCommentsData
     * @return void
     */
    public function shouldReturnComments($commentData)
    {
        $id = self::$mongologue->post('create', new \Mongologue\Models\Post($commentData));
        $retrievedPost = self::$mongologue->post('find', $commentData["parent"]);
        $commentData["id"] = $id;
        $comments = self::$mongologue->post('getComments', $commentData["parent"]);
        foreach ($comments as $comment) {
            if ($comment["id"]==$id) {
                if ($comment["content"]==$commentData["content"]) {
                    if ($comment["datetime"]==$commentData["datetime"]) {
                        return;
                    }
                }
            }
        }
        $this->fail("Comments did not return properly");
    }

    /**
     * should Create Category And Retrieve Id
     * 
     * @param array $categoryData category data
     *
     * @test
     *
     * @dataProvider provideValidCategoryData
     * @return void
     */
    public function shouldCreateCategoryAndRetrieveId($categoryData)
    {
        $id = self::$mongologue->category('create', new \Mongologue\Models\Category($categoryData));
        $retrievedCategory = self::$mongologue->category('find', $id);
        $this->assertEquals($categoryData["name"], $retrievedCategory["name"]);
    }

    /**
     * should Retrieve User Feeds
     *
     * @test
     * 
     * @return void
     */
    public function shouldRetrieveUserFeeds()
    {
        $user1 = array(
            "id"=>"1238899884791",
            "handle"=>"jdoe_1",
            "email"=>"jdoe1@x.com",
            "firstName"=>"John_1",
            "lastName"=>"Doe"
        );
        $user2 = array(
            "id"=>"1238899884792",
            "handle"=>"jdoe_2",
            "email"=>"jdoe2@x.com",
            "firstName"=>"John_2",
            "lastName"=>"Doe"
        );
        $user3 = array(
            "id"=>"1238899884793",
            "handle"=>"jdoe_3",
            "email"=>"jdoe3@x.com",
            "firstName"=>"John_3",
            "lastName"=>"Doe"
        );
        $user4= array(
            "id"=>"1238899884794",
            "handle"=>"jdoe_4",
            "email"=>"jdoe2@x.com",
            "firstName"=>"John_4",
            "lastName"=>"Doe"
        );

        $group1 = array(
            "name" => "Cool Group 1"
        );

        $group2 = array(
            "name" => "Cool Group 2"
        );

        $group3 = array(
            "name" => "Cool Group 3"
        );


        $group1["id"] = self::$mongologue->group('register', new \Mongologue\Models\Group($group1));
        $group2["id"] = self::$mongologue->group('register', new \Mongologue\Models\Group($group2));
        $group3["id"] = self::$mongologue->group('register', new \Mongologue\Models\Group($group3));

        self::$mongologue->user('register', new \Mongologue\Models\User($user1));
        self::$mongologue->user('register', new \Mongologue\Models\User($user2));
        self::$mongologue->user('register', new \Mongologue\Models\User($user3));
        self::$mongologue->user('register', new \Mongologue\Models\User($user4));

        self::$mongologue->group("join", $group1["id"], $user1["id"]);
        self::$mongologue->group("join", $group1["id"], $user2["id"]);
        self::$mongologue->group("join", $group2["id"], $user3["id"]);
        self::$mongologue->group("join", $group3["id"], $user4["id"]);

        self::$mongologue->user('follow', $user3["id"], $user1["id"]);
        self::$mongologue->user('follow', $user2["id"], $user1["id"]);
        self::$mongologue->user('follow', $user1["id"], $user3["id"]);

        self::$mongologue->group('follow', $group3["id"], $user2["id"]);
        self::$mongologue->group('follow', $group1["id"], $user3["id"]);
        self::$mongologue->group('follow', $group1["id"], $user4["id"]);
        
        $post1 = array(
            "userId"=>$user1["id"],
            "datetime"=>"12.01.2014",
            "content"=>"user one",
            "filesToBeAdded" => array(
                __DIR__."/../resources/sherlock.jpg"=>array(
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
                __DIR__."/../resources/sherlock.jpg"=>array(
                    "type"=>"jpeg",
                    "size"=>"100"
                )
            )
        );

        $postId1 = self::$mongologue->post('create', new \Mongologue\Models\Post($post1));
        $postId2 = self::$mongologue->post('create', new \Mongologue\Models\Post($post2));
        $postId3 = self::$mongologue->post('create', new \Mongologue\Models\Post($post3));

        $messages = array("user one", "user four");

        $res = self::$mongologue->post('find', $postId2);
        $this->assertEquals($post2["content"], $res["content"]);

        $feed = self::$mongologue->inbox('feed', $user2["id"]);

        $this->assertEquals(2, count($feed));

        foreach ($feed as $key => $post) {
            $this->assertEquals($user2["id"], $post["recipient"]);
            $this->assertContains($post["content"], $messages);
        }

    }

    /**
     * provide Valid User Data
     * 
     * @return array Valid User Data
     */
    public function provideValidUserData()
    {
        return array(
            array(
                array(
                    "id" => 40,
                    "handle" => "tommy",
                    "firstName" => "Tommy",
                    "lastName" => "Jones",
                    "email" => "tjones@pirates.com"
                ),
                array(
                    "id" => 40,
                    "handle" => "tommy",
                    "firstName" => "Tommy",
                    "lastName" => "Jones",
                    "email" => "tjones@pirates.com",
                    "pic" => null,
                    "following" => array(),
                    "followers" => array(),
                    "groups" => array(),
                    "blocking" => array(),
                    "followingGroups" => array(),
                    "likes" => array(),
                    "data" => array()
                )
            ),
            array(
                array(
                    "id" => 440,
                    "handle" => "ben",
                    "firstName" => "Ben",
                    "lastName" => "Hur",
                    "email" => "ben@hur.com",
                    "pic" => "thispic.com",
                    "groups" => array(1,2)
                ),
                array(
                    "id" => 440,
                    "handle" => "ben",
                    "firstName" => "Ben",
                    "lastName" => "Hur",
                    "email" => "ben@hur.com",
                    "pic" => "thispic.com",
                    "following" => array(),
                    "followers" => array(),
                    "groups" => array(1,2),
                    "blocking" => array(),
                    "followingGroups" => array(),
                    "likes" => array(),
                    "data" => array()
                )
            )
        );
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
                array("name" => "Scientists"),
                array(
                    "id"=>'1',
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
                array("name"=>"Botanist"),
                array(
                    "id"=>'2',
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
                array("name" => "Physicist", "parent"=>1),
                array(
                    "id"=>'3',
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
                array("name"=> "Botanist", "parent"=>1),
                array(
                    "id"=>'4',
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

    /**
     * providerValidPostData 
     * 
     * @return array valid post Data
     */
    public function providerValidPostData()
    {
        return array(
            array(
                array(
                    "userId"=>40,
                    "datetime"=>"12.01.2014",
                    "content"=>"hello testing1",
                    "filesToBeAdded" => array(
                        __DIR__."/../resources/sherlock.jpg"=>array(
                            "type"=>"jpeg",
                            "size"=>"100"
                        )
                    )
                )
            ),
            array(
                array(
                    "userId"=>40,
                    "datetime"=>"12.01.2014",
                    "content"=>"hello testing2",
                    "filesToBeAdded" => array(
                        __DIR__."/../resources/sherlock.jpg"=>array(
                            "type"=>"jpeg",
                            "size"=>"100"
                        )
                    )
                )
            )
        );
    }

    /**
     * providerValidCommentsData 
     * 
     * @return array valid comments Data
     */
    public function providerValidCommentsData()
    {
        return array(
            array(
                array(
                    "userId"=>40,
                    "datetime"=>"12.01.2014",
                    "content"=>"hello testing comment",
                    "parent"=>1,
                    "type"=>"comment",
                    "filesToBeAdded" => array(
                        __DIR__."/../resources/sherlock.jpg"=>array(
                            "type"=>"jpeg",
                            "size"=>"100"
                        )
                    )
                )
            ),
            array(
                array(
                    "userId"=>40,
                    "datetime"=>"12.01.2014",
                    "content"=>"hello testing comment 2",
                    "parent"=>1,
                    "type"=>"comment",
                    "filesToBeAdded" => array(
                        __DIR__."/../resources/sherlock.jpg"=>array(
                            "type"=>"jpeg",
                            "size"=>"100"
                        )
                    )
                )
            )
        );
    }

    /**
     * provideValidCategoryData
     * 
     * @return array valid category data
     */
    public function provideValidCategoryData()
    {
        return array(
            array(
                array(
                    "name"=>"Hello"
                )
            ),
            array(
                array(
                    "name"=>"Naveen"
                )
            )
        );
    }
}
