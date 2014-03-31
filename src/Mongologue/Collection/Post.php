<?php
/**
 * File Containing the Posts Collection
 *
 * @category Mongologue
 * @package  Collection
 * @author   @tkannippoyil <tkannippoyil@suyati.com>
 * @license  none http://suyati.com
 * @link     http://github.com/xait/docxwriter
 */
namespace Mongologue\Collection;

use \Mongologue\Interfaces\Collection;
use \Mongologue\Models;
use \Mongologue\Core\Collections;

/**
 * Class Managing Collection of Posts
 *
 * @category Mongologue
 * @package  Collection
 * @author   @tkannippoyil <tkannippoyil@suyati.com>
 * @license  none http://suyati.com
 * @link     http://github.com/xait/docxwriter
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
        $followers = $user->followers();
        $groups = $user->groups();

        foreach ($groups as $groupId) {
            $group = $this->_collections->getCollectionFor("group")->modelFromId($groupId);
            $members = $group->getMembers();
            $groupFollowers = $group->getFollowers();

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

        $post->setRecipients(array_diff($followers, array($user->id())));
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
        foreach ($post->_filesToBeAdded as $name => $attributes) {
            $post->addFile($grid->storeFile($name, $attributes));
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
                $parent = $this->modelFromID($post->parent());
                $parent->addComment($post->id);
                $this->update($parent);
            }
            
            $this->saveFiles($post);
            $this->_collection->insert($post);
        }

        return $post->id;
    }

    /**
     * Create Post
     *
     * @param array $post Details Post to be Created
     *
     * @access public
     * @return bool True if success
     */
    public function create(Models\Post $post)
    {
        $user = $this->_collections->getCollectionFor("user")->modelFromId($post->userId);
        $post->setId(
            $this->_collections->getCollectionFor("counters")->nextPostId()
        );
        
        $this->_findRecipients($post, $user);

        $this->_collections->getCollectionFor("inbox")->writeToInbox($post);
        
        $this->savePost($post);        
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

        $this->_collection->update(
            array("id" => $post->id()),
            $post->document()
        );
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
        if($post)
            return new Models\Post($post);
        else
            throw new Exceptions\Post\PostNotFoundException("Post with ID $id not found");
    }

    /**
     * Register a New Post
     * 
     * @param Models\Post $post Model of the Post to be Registered
     *
     * @throws DuplicatePostException when an existing post id is provided
     * @return bool True if Success
     */
    public function register(Models\Post $post)
    {
        try
        {
            $temp = $this->modelFromId($post->id());
        }
        catch(Exceptions\Post\PostNotFoundException $e)
        {
            $this->_collection->insert($post->document());
            return true;
        }

        throw new Exceptions\Post\DuplicatePostException("Post with this ID already registered");
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

        foreach ($$cursor as $document) {
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

        if($post)
            return new Models\Post($post);
        else
            throw new Exceptions\Post\PostNotFoundException("No Post Matching Query");
            
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
