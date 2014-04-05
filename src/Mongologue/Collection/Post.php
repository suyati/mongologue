<?php
/**
 * File Containing the Posts Collection
 *
 * @category Mongologue
 * @package  Collection
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @author   @tkannippoyil <tkannippoyil@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
namespace Mongologue\Collection;

use \Mongologue\Interfaces\Collection;
use \Mongologue\Models;
use \Mongologue\Core\Collections;
use \Mongologue\Exceptions;

/**
 * Class Managing Collection of Posts
 *
 * @category Mongologue
 * @package  Collection
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @author   @tkannippoyil <tkannippoyil@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
class Post implements Collection
{
    private $_collection;
    private $_collections;
    private $_grid;

    /**
     * Constructor function
     *
     * @param MongoColleciton $mongoCollection Mongo Collection Object
     * @param Collections     $collections     Post of Collecitons
     */
    public function __construct(\MongoCollection $mongoCollection, Collections $collections)
    {
        $this->_collections = $collections;
        $this->_collection = $mongoCollection;
    }

    /**
     * Set a Grid FS to store the Files
     * 
     * @param MongoGridFS $grid MongoGridFS Object
     *
     * @return void
     */
    public function setGridFS(\MongoGridFS $grid)
    {
        $this->_grid = $grid;
    }

    /**
     * Find Recipients of Post
     * 
     * @param self        $post Post
     * @param Models\User $user User Model
     * 
     * @return boolean True if Success
     */
    private function _findRecipients(Models\Post $post, Models\User $user)
    {
        $followers = $user->followers;
        $groups = $user->groups;

        foreach ($groups as $groupId) {
            $group = $this->_collections->getCollectionFor("groups")->modelFromId($groupId);
            $members = $group->members;
            $groupFollowers = $group->followers;

            $members = array_merge(
                array_intersect($members, $groupFollowers),
                array_diff($members, $groupFollowers),
                array_diff($groupFollowers, $members)
            );

            $followers = array_merge(
                array_intersect($members, $followers),
                array_diff($members, $followers),
                array_diff($followers, $members)
            );
        }

        $post->setRecipients(array_diff($followers, array($user->id)));
        return true;
    }


    

    /**
     * Save FIles in a Post
     * 
     * @param Models\Post $post Post Model
     * 
     * @return void
     */
    public function saveFiles(Models\Post $post)
    {
        foreach ($post->filesToBeAdded() as $name => $attributes) {
            $post->addFile($this->_grid->storeFile($name, $attributes));
        }
    }


    /**
     * Save a Post into the COllection
     * 
     * @param Post $post Post to be Saved
     * 
     * @return boolean True if Success
     */
    public function savePost(Models\Post $post)
    {
        $tempPost = $this->_collection->findOne(array("id"=> $post->id));
        if ($tempPost) {
            throw new Exceptions\Post\DuplicatePostException("Post Id already Added");
        } else {
            if ($post->isComment()) {
                $parent = $this->modelFromID((integer)$post->parent);
                $parent->addComment($post->id);
                $this->update($parent);
            }
            
            $this->saveFiles($post);
            $this->_collection->insert($post->document());
        }

        return $post->id;
    }

    /**
     * Create Post
     *
     * @param array $post Details Post to be Created
     *
     * @access public
     * @return string Id of the Post
     */
    public function create(Models\Post $post)
    {
        $user = $this->_collections->getCollectionFor("users")->modelFromId($post->userId);
        $post->setId(
            $this->_collections->getCollectionFor("counters")->nextId("posts")
        );
        
        $this->_findRecipients($post, $user);

        $this->_collections->getCollectionFor("inbox")->writeToInbox($post);
        
        return $this->savePost($post);
    }

    /**
     * Update document of a Post
     * 
     * @param Models\Post $post Model of a post
     * 
     * @return void
     */
    public function update(Models\Post $post)
    {
            // print_r($post->id);exit();
        $this->_collection->update(
            array("id" => $post->id),
            $post->document()
        );
    }

    /**
     * Find a Post
     * 
     * @param mixed $param Parameter to Find. Pass an Id or a query
     * 
     * @return array document for the post
     */
    public function find($param)
    {
        if (is_array($param)) {
            return $this->modelFromQuery($param)->document();
        } else {
            return $this->modelFromId($param)->document();
        }
    }

    /**
     * Get a Post Model from a Post Id
     * 
     * @param string $id Id of the Post
     *
     * @throws PostNotFoundException when Invalid id is provided
     * @return Models\Post Model of the Post
     */
    public function modelFromId($id)
    {
        $post = $this->_collection->findOne(array("id" => $id));
        if ($post) {
            return new Models\Post($post);
        } else {
            throw new Exceptions\Post\PostNotFoundException("Post with ID $id not found");
        }
    }


    /**
     * Get All Posts
     * 
     * @return array List of All Posts
     */
    public function all()
    {
        $posts = array();
        $cursor = $this->_collection->find();

        foreach ($cursor as $document) {
            $posts[] = new Models\Post($document);
        }

        return $posts;
    }

    /**
     * Get All maching Posts
     * 
     * @return array List of All maching Posts
     */
    public function search($query)
    {
        $posts = array();
        $cursor = $this->_collection->find($query);

        foreach ($cursor as $document) {
            $posts[] = new Models\Post($document);
        }

        return $posts;
    }


    /**
     * Get a Post Model using a Query
     * 
     * @param array $query Query for the model
     *
     * @throws PostNotFoundException if a Post which matches criteria cannot be found
     * @return Models\Post Model of the matching post
     */
    public function modelFromQuery(array $query)
    {
        $post = $this->_collection->findOne($query);

        if ($post) {
            return new Models\Post($post);
        } else {
            throw new Exceptions\Post\PostNotFoundException("No Post Matching Query");
        }
            
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
    public function like($postId, $likedUserId)
    {
        $post = $this->modelFromId($postId);
        if (in_array($likedUserId, $post->likes)) {
            throw new Exceptions\Post\AlreadyLikesThisPostException("User with ID $likedUserId is already being liked this post");
        } else {
            $this->_addLikedPostsToUser($likedUserId, $post->id);
            $post->addLikes($likedUserId);
            $this->update($post);
            return true;
        }
    }

    /**
     * getComment 
     * Get the Comment details
     * 
     * @param string $postId contain post id
     *  
     * @return Array of Comments 
     */
    public function getComments($postId)
    {
        $comments = array();
        $posts = $this->search(array("parent" => $postId));
        foreach ($posts as $post) {
            $comments[] = $post->document();
        }
        return $comments;
    }

    /**
     * getImage 
     * Get the Image of the post
     * 
     * @param string $fileId contain post id
     *  
     * @return Array of Image 
     */
    public function getImage(\MongoId $fileId)
    {
        return $this->_grid->findOne(array('_id' =>$fileId));
    }

    /**
     * Addliked Posts in to user collection
     * 
     * @param string $likedUserId Id of the User
     * @param string $likedPostId Id of the Post
     *
     * @throws UserNotFoundException if the user with the provided id does not exist
     *                               in the colleciton.
     *                               
     * @return User Instance of a User
     */
    private function _addLikedPostsToUser($likedUserId, $likedPostId)
    {
        $user_collection = $this->_collections->getCollectionFor("users");
        $user = $user_collection->modelFromId($likedUserId);
        if (in_array($likedPostId, $user->likes)) {
            throw new Exceptions\Post\AlreadyAddedPostIdException("User with ID $likedPostId is already being liked by this user");
        } else {
                    
            $user->setLikes($likedPostId);
            $user_collection->update($user);
            return true;
        }
    }

    /**
     * Execute a Command and return the Results
     * 
     * @param string $callable A function of the instance
     * @param array  $params   Parameters to be passed to the instance
     * 
     * @return mixed Result of the Funciton
     */
    public function execute($callable, array $params)
    {
        return call_user_func_array(array($this, $callable), $params);
    }
}
