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
        $id = self::$mongologue->group('register', new \Mongologue\Models\Group($groupData));

        $this->assertEquals($expectedDocument, self::$mongologue->group('find', $id));
        $this->assertEquals($expectedDocument, self::$mongologue->group('find', $query));
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
     * @test
     *
     * @return void
     */
    public function shouldLikePost()
    {
        self::$mongologue->post('like', 1, 40);
        $retrievedPost = self::$mongologue->post('find', 1);
        $this->assertEquals(array("40"), $retrievedPost["likes"]);

        $retrievedUser = self::$mongologue->user('find', 40);
        $this->assertEquals(array(1), $retrievedUser["likes"]);

        self::$mongologue->post('like', 2, 40);
        $retrievedPost = self::$mongologue->post('find', 2);
        $this->assertEquals(array("40"), $retrievedPost["likes"]);
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
