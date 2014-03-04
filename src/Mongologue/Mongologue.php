<?php
/**
 * File Containig the COre Mongologue Class
 *
 * @category Mongologue
 * @package  Core
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  NONE http://suyati.com
 * @version  0.1.1
 * @link     http://suyati.com
 */
namespace Mongologue;

use \Mongologue\Config;
use \Mongologue\User;
use \Mongologue\Post;
use \Mongologue\Group;
use \Mongologue\Category;

/**
 * Core Mongologue Class
 *
 * @category Mongologue
 * @package  Core
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  NONE http://suyati.com
 * @version  0.1.1
 * @link     http://suyati.com
 */
class Mongologue
{
    private $_client;
    private $_db;
    private $_userCollection;
    private $_groupCollection;
    private $_commentsCollection;
    private $_postCollection;
    private $_categoryCollection;
    private $_inboxCollection;
    private $_grid;
    
    /**
     * Constructor of the Class
     * 
     * @param MongoClient $client Accepts a MongoClient
     * @param string      $dbName Name of the Database
     */
    public function __construct(\MongoClient $client, $dbName=null)
    {
        if(is_null($dbName))
            $dbName = Config::DB_NAME;

        $userCollection = Config::USER_COLLECTION;
        $commentCollection = Config::COMMENTS_COLLECTION;
        $postCollection = Config::POST_COLLECTION;
        $groupCollection = Config::GROUP_COLLECTION;
        $categoryCollection = Config::CATEGORY_COLLECTION;
        $inboxCollection = Config::INBOX_COLLECTION;

        $this->_client = $client;
        $this->_db = $this->_client->$dbName;
        
        //Setting up Counters for Incremental Ids
        $this->_countersCollection = $this->_db->createCollection("counters");

        $this->_userCollection = $this->_db->createCollection($userCollection);
        $this->_groupCollection = $this->_db->createCollection($groupCollection);
        $this->_commentsCollection = $this->_db->createCollection($commentCollection);
        $this->_postCollection = $this->_db->createCollection($postCollection);
        $this->_categoryCollection = $this->_db->createCollection($categoryCollection);
        $this->_inboxCollection = $this->_db->createCollection($inboxCollection);
        $this->_grid = $this->_db->getGridFS();

    }

    /**
     * Register A user in the System
     * 
     * @param User  $user   Details of User to be Registered
     * @param Array $groups A list of groups that the member is a part of
     * 
     * @access public
     * @return bool true if success
     */
    public function registerUser(User $user, $groups = array())
    {
        User::registerUser($user, $this->_userCollection);
        foreach ($groups as $groupId) {
            $user->joinGroup($groupId, $this->_userCollection, $this->_groupCollection);
        }
        return true;        
        
    }

    /**
     * Register A Group 
     * 
     * @param Group $group Group to be Registered
     * 
     * @return bool true if success
     */
    public function registerGroup(Group $group)
    {       
        Group::registerGroup($group, $this->_groupCollection);
        return true;        
    }

    /**
     * Register A Category 
     * 
     * @param Category $category Category to be Registered
     * 
     * @return bool true if success
     */
    public function registerCategory(Category $category)
    {       
            Category::registerCategory($category, $this->_categoryCollection);
            return true;        
    }

    /**
     * Create Post
     *
     * @param array $post Details Post to be Created
     *
     * @access public
     * @return bool True if success
     */
    public function createPost(array $post)
    {
        $user = User::fromID($post["userId"], $this->_userCollection);
        $id = Post::getNextPostId($this->_countersCollection);
        $post["id"] = $id;
        $post = new Post($post);
        Post::findRecipients($post, $this->_postCollection, $this->_userCollection, $this->_groupCollection);
        Inbox::writeToInbox($post, $this->_inboxCollection, $this->_postCollection, $this->_userCollection, $this->_groupCollection);
        return Post::savePost($post, $this->_grid, $this->_postCollection);
    }

    /**
     * Follow a User
     * 
     * @param string $followeeId Id of the User that is to be Followed
     * @param string $followerId Id of the User who is following
     *
     * @access public
     * @return bool True if Success
     */
    public function followUser($followeeId, $followerId)
    {
        $user = User::fromID($followerId, $this->_userCollection);
        return $user->followUser($followeeId, $this->_userCollection);
    }

    /**
     * unFollow a User
     * 
     * @param string $followeeId Id of the User that is to be Followed
     * @param string $followerId Id of the User who is following
     *
     * @access public
     * @return bool True if Success
     */
    public function unFollowUser($followeeId, $followerId)
    {
        $user = User::fromID($followerId, $this->_userCollection);
        return $user->unFollowUser($followeeId, $this->_userCollection);
    }

    /**
     * Follow a Group
     * 
     * @param string $groupId    Id of the Group to be followed
     * @param string $followerId Id of the User who is following
     *
     * @access public
     * @return bool True if Success
     */
    public function followGroup($groupId, $followerId)
    {
        $user = User::fromID($followerId, $this->_userCollection);
        return $user->followGroup($groupId, $this->_userCollection, $this->_groupCollection);
    }

    /**
     * unFollow a Group
     * 
     * @param string $groupId    Id of the Group to be followed
     * @param string $followerId Id of the User who is following
     *
     * @access public
     * @return bool True if Success
     */
    public function unFollowGroup($groupId, $followerId)
    {
        $user = User::fromID($followerId, $this->_userCollection);
        return $user->unFollowGroup($groupId, $this->_userCollection, $this->_groupCollection);
    }

    /**
     * Join a Group
     * 
     * @param string $userId  Id of the User
     * @param string $groupId Id of the group
     * 
     * @return bool True if success
     */
    public function joinGroup($userId, $groupId)
    {
        $user = User::fromID($userId, $this->_userCollection);
        return $user->joinGroup($groupId, $this->_userCollection, $this->_groupCollection);
    }

    /**
     * Leave a Group
     * 
     * @param string $userId  Id of the User
     * @param string $groupId Id of the group
     * 
     * @return bool True if success
     */
    public function leaveGroup($userId, $groupId)
    {
        $user = User::fromID($userId, $this->_userCollection);
        return $user->leaveGroup($groupId, $this->_userCollection, $this->_groupCollection);
    }

    /**
     * Get Members of a group
     * 
     * @param string $groupId Id of the group
     * 
     * @return array List of member ids
     */
    public function getGroupMembers($groupId)
    {
        return Group::fromID($groupId, $this->_groupCollection)->getMembers();
    }

    /**
     * Get Followers of a group
     * 
     * @param string $groupId Id of the group
     * 
     * @return array List of member ids
     */
    public function getGroupFollowers($groupId)
    {
        return Group::fromID($groupId, $this->_groupCollection)->getFollowers();
    }

    /**
     * Get the Feed for a User
     * 
     * @param string  $userId Id of the User
     * @param integer $limit  Max number of Posts
     * @param integer $since  Since which posts
     *
     * @access public
     * @return array JSON Feed for the User
     */
    public function getFeed($userId, $limit=null, $since=null)
    {
        return Inbox::getMessages($userId, $this->_inboxCollection, $limit, $since);
    }

    /**
     * Get All the Users in the System
     * 
     * @return array List of Users
     */
    public function getAllUsers()
    {
        $users = array();
        $cursor = $this->_userCollection->find();

        foreach ($cursor as $document) {
            $users[] = User::fromDocument($document);
        }

        return $users;
    }

    /**
     * Get the User from the ID
     * 
     * @param string $userId Id of the USer
     * 
     * @return User Instance of the USer
     */
    public function getUser($userId)
    {
        return User::fromID($userId, $this->_userCollection);
    }

    /**
     * Get the Followers of the User
     * 
     * @param string $userId Id of the user
     * 
     * @return array Id of followers of the User
     */
    public function getFollowers($userId)
    {
        return User::fromID($userId, $this->_userCollection)->followers();
    }

    /**
     * Get the Users that a User follow
     * 
     * @param string $userId Id of the user
     * 
     * @return array list of users that the user follows
     */
    public function getFollowingUsers($userId)
    {
        return User::fromID($userId, $this->_userCollection)->followingUsers();
    }

    /**
     * Get the Groups that a User follow
     * 
     * @param string $userId Id of the user
     * 
     * @return array list of groups that the user follows
     */
    public function getFollowingGroups($userId)
    {
        return User::fromID($userId, $this->_userCollection)->followingGroups();
    }

    /**
     * Get All the Groups
     * 
     * @return array List of all groups
     */
    public function getAllGroups()
    {
        $groups = array();

        $cursor = $this->_groupCollection->find();

        foreach ($cursor as $document) {
            $groups[] = Group::fromDocument($document);
        }

        return $groups;
    }

    /**
     * Get a Group from ID
     * 
     * @param string $id Id of the Group
     * 
     * @return Group A Group that matches the Id
     */
    public function getGroup($id)
    {
        return Group::fromID($id, $this->_groupCollection);
    }

    /**
     * Get a Post form ID
     * 
     * @param string $id Id of the post
     * 
     * @return Post Object of the post
     */
    public function getPost($id)
    {
        return Post::fromID($id, $this->_postCollection);
    }

    /**
     * Get a File from the Grid FS
     * 
     * @param Mongoid $id Id of the File
     * 
     * @return mixed File from the Grid
     */
    public function getFile(\Mongoid $id)
    {
        return $this->_grid->get($id);
    }

    /**
     * Get All the Categories
     * 
     * @return array List of all Categories
     */
    public function getAllCategories()
    {
        $categories = array();

        $cursor = $this->_categoryCollection->find();

        foreach ($cursor as $document) {
            $categories[] = Category::fromDocument($document);
        }

        return $categories;
    }

    /**
     * Get a Category from ID
     * 
     * @param string $id Id of the Category
     * 
     * @return Category A Category that matches the Id
     */
    public function getCategory($id)
    {
        return Category::fromID($id, $this->_categoryCollection);
    }
}
?>