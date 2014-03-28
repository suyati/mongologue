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
use \Mongologue\Premadepost;

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
    private $_premadepostCollection;
    
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
        $premadepostCollection = Config::PREMADEPOST_COLLECTION;

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
        $this->_premadepostCollection = $this->_db->createCollection($premadepostCollection);
        $this->_grid = $this->_db->getGridFS();

    }
    /**
     * [userCollection description]
     * @return [type] [description]
     */
    public function userCollection() {
        return $this->_userCollection;
    }
    /**
     * [userCollection description]
     * @return [type] [description]
     */
    public function postCollection() {
        return $this->_postCollection;
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
     * Update A Group 
     * 
     * @param Group $group Group to be Registered
     * 
     * @return bool true if success
     */
    public function updateGroup(Group $group)
    {       
        Group::updateGroup($group, $this->_groupCollection);
        return true;        
    }

    /**
     * Remove A Group 
     * 
     * @param string $groupId Group to be Registered
     * 
     * @return bool true if success
     */
    public function removeGroup($groupId)
    {       
        Group::removeGroup($groupId, $this->_groupCollection);
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
     * Update A Category 
     * 
     * @param Category $category Category to be Registered
     * 
     * @return bool true if success
     */
    public function updateCategory(Category $category)
    {       
        Category::updateCategory($category, $this->_categoryCollection);
        return true;        
    }

    /**
     * Remove A Category 
     * 
     * @param string $categoryId Category to be Registered
     * 
     * @return bool true if success
     */
    public function removeCategory($categoryId)
    {       
        Category::removeCategory($categoryId, $this->_categoryCollection);
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
        $post["id"] = (string)$id;
        $post = new Post($post);
        Post::findRecipients($post, $this->_postCollection, $this->_userCollection, $this->_groupCollection);
        Inbox::writeToInbox($post, $this->_inboxCollection, $this->_postCollection, $this->_userCollection, $this->_groupCollection);
        return Post::savePost($post, $this->_grid, $this->_postCollection);
        
    }

    /**
     * Create Comment
     *
     * @param array $comment Details of Comment to be Created
     *
     * @access public
     * @return bool True if success
     */
    public function createComment(array $comment)
    {
        $user = User::fromID($comment["userId"], $this->_userCollection);
        $id = Post::getNextPostId($this->_countersCollection);
        $comment["id"] = (string)$id;
        $comment = new Post($comment);
        Post::findRecipients($comment, $this->_postCollection, $this->_userCollection, $this->_groupCollection);
        Inbox::writeToInbox($comment, $this->_inboxCollection, $this->_postCollection, $this->_userCollection, $this->_groupCollection);
        return Post::savePost($comment, $this->_grid, $this->_postCollection);
        
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
     * Follow a Group
     * 
     * @access public
     * @return bool True if Success
     */
    public function followGroups($group)
    {
        $user = User::fromID($group["id"], $this->_userCollection);
        $groupIds = $group["groupId"];
        foreach ($groupIds as $groupId) {
            $user->followGroup($groupId, $this->_userCollection, $this->_groupCollection);
        }
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
     * @param integer $page page number
     * @param integer $rows per page
     * 
     * @return array List of all groups
     */
    public function getAllGroups($page, $rows)
    {
        $groups = array();

        $cursor = $this->_groupCollection->find()->skip(($page-1)*$rows)->limit($rows);

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
     * @param string $postId Id of the post
     * 
     * @return Post Object of the post
     */
    public function getPost($postId)
    {
        return Post::fromID($postId, $this->_postCollection);
    }

    /**
     * Get a File from the Grid FS
     * 
     * @param Mongoid $id Id of the File
     * 
     * @return mixed File from the Grid
     */
    public function getFile(\MongoId $id)
    {
        return $this->_grid->findOne(array('_id' =>$id));;
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

    /**
     * Register A Premadepost 
     * 
     * @param Premadepost $premadepost Premadepost to be Registered
     * 
     * @return bool true if success
     */
    public function registerPremadepost(Premadepost $premadepost)
    {       
            Premadepost::registerPremadepost($premadepost, $this->_premadepostCollection);
            return true;        
    }


    /**
     * Update A Premadepost 
     * 
     * @param Premadepost $premadepost Premadepost to be Registered
     * 
     * @return bool true if success
     */
    public function updatePremadepost(Premadepost $premadepost)
    {       
        Premadepost::updatePremadepost($premadepost, $this->_premadepostCollection);
        return true;        
    }

    /**
     * Remove A Premadepost 
     * 
     * @param string $premadepostId Premadepost to be Registered
     * 
     * @return bool true if success
     */
    public function removePremadepost($premadepostId)
    {       
        Premadepost::removePremadepost($premadepostId, $this->_premadepostCollection);
        return true;        
    }
    


    /**
     * Get All the Premadeposts
     * 
     * @return array List of all Premadeposts
     */
    public function getAllPremadepost()
    {
        $premadeposts = array();

        $cursor = $this->_premadepostCollection->find();

        foreach ($cursor as $document) {
            $premadeposts[] = Premadepost::fromDocument($document);
        }

        return $premadeposts;
    }

     /**
     * Like User Post
     * 
     * @param string $postId      Id of the User post
     * 
     * @param string $likedUserId Id of the post liked user.
     *
     * @access public
     * @return bool True if Success
     */
    public function likePost($postId, $likedUserId)
    {
        $post = Post::fromID($postId, $this->_postCollection);
        return $post->likePost($likedUserId, $this->_postCollection, $this->_userCollection);
    }

    /**
     * Like User Post
     * 
     * @param string $postId Id of the User post
     *
     * @access public
     * @return Array of UserId
     */
    public function getLikes($postId)
    {
        $post = Post::fromID($postId, $this->_postCollection);
        return $post->likedUsers();
    }


    /**
     * Like User Post
     * 
     * @param string $postId Id of the User post
     *
     * @access public
     * @return Array count of UserId
     */
    public function getLikesCount($postId)
    {
        return Post::fromID($postId, $this->_postCollection);
    }

    /**
     * Get Comments of Post
     * 
     * @param string $postId Id of the User post
     *
     * @access public
     * @return Array of CommentIds
     */
    public function getComments($postId)
    {
        $post = Post::fromID($postId, $this->_postCollection);
        return $post->getComments($this->_postCollection);
    }

    /**
     * Get The Mutual Friends
     * 
     * @param string $userId User Id 
     * 
     * @access public
     * @return Array of the Mutual Friends
     */
    public function getMutualfriends($userId)
    {
        return User::fromID($userId, $this->_userCollection);
    }

    /**
     * Get the Mutual Likes
     * 
     * @param string $userId User Id
     * 
     * @return Array of Mutual Likes
     */
    public function getMutualLikes($userId)
    {
        return User::fromID($userId, $this->_userCollection);
    }
}
?>