<?php
/**
 * File Containing the Post Class
 *
 * @category Mongologue
 * @package  Core
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  NONE http://suyati.com
 * @version  0.1.1
 * @link     http://suyati.com
 */
namespace Mongologue;

/**
 * Class Managing Posts
 *
 * @category Mongologue
 * @package  Core
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @author   @naveenbos <nmohanan@suyati.com>
 * @license  NONE http://suyati.com
 * @version  0.1.1
 * @link     http://suyati.com
 */
class Post
{
    private $_id;
    private $_content;
    private $_userId;
    private $_files = array();
    private $_parent;
    private $_likes = array();
    private $_comments = array();
    private $_type;
    private $_postType;
    private $_datetime;
    private $_category;
    private $_recipients = array();

    private $_filesToBeAdded = array();

    /**
     * Collection of Counters for Sequential Id
     * @var [type]
     */
    private $_counters;

    /**
     * Get the Next Post Id
     * 
     * @param MongoCollection $counters Collection of Counters
     * 
     * @return integer The Id for the Post Id next in Sequence
     */
    public static function getNextPostId(\MongoCollection $counters)
    {
        $count = $counters->findAndModify(
            array("id"=>"posts"),
            array('$inc'=>array("s"=>1)),
            null,
            array("upsert"=>true, "new"=>true)
        );
        return $count["s"];
    }

    /**
     * Find Recipients of Post
     * 
     * @param self            $post            Post
     * @param MongoCollection $postCollection  Post Collection
     * @param MongoCollection $userCollection  User Collection
     * @param MongoCollection $groupCollection Group Collection
     * 
     * @return boolean True if Success
     */
    public static function findRecipients(self $post, \MongoCollection $postCollection, \MongoCollection $userCollection, \MongoCollection $groupCollection)
    {
        $user = User::fromID($post->getUserId(), $userCollection);
        $followers = $user->followers();
        $groups = $user->groups();

        foreach ($groups as $groupId) {
            $group = Group::fromID($groupId, $groupCollection);
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
     * Get a Post form Id
     * 
     * @param string          $postId     Id of the Post
     * @param MongoCollection $collection Collection of Posts
     * 
     * @return Post Object of the Post
     */
    public static function fromID($postId, \MongoCollection $collection)
    {
        $post = $collection->findOne(array("id"=>$postId));
        if($post)
            return new self($post);
        else
            throw new Exceptions\Post\PostNotFoundException("Post with Id $postId not Found");
            
    }

    /**
     * Save a Post into the COllection
     * 
     * @param Post            $post       Post to be Saved
     * @param MongoGridFS     $grid       Grid FS for Images
     * @param MongoCollection $collection Post Collection
     * 
     * @return boolean True if Success
     */
    public static function savePost(self $post, \MongoGridFS $grid, \MongoCollection $collection)
    {
        $tempPost = $collection->findOne(array("id"=> $post->id()));
        if ($tempPost) {
            throw new Exceptions\Post\DuplicatePostException("Post Id already Added");
        } else {
            if ($post->isComment()) {
                $parent = self::fromID($post->parent(), $collection);
                $parent->addComment($post->id());
                $parent->update($collection);
            }
            
            $post->saveFiles($grid);
            $collection->insert($post->document());
        }

        return $post->id();
    }

    /**
     * Constructor of Class
     * 
     * @param array $post Details of Post
     */
    public function __construct(array $post)
    {
        $this->_userId = $post["userId"];
        $this->_datetime = $post["datetime"];
        $this->_content = $post["content"];
        $this->_id = $post["id"];

        if (isset($post["files"])) {
            $this->_files = $post["files"];
        }
        if (isset($post["parent"])) {
            $this->_parent= $post["parent"];    
        }
        if (isset($post["likes"])) {
            $this->_likes = $post["likes"]; 
        }
        if (isset($post["comments"])) {
            $this->_comments = $post["comments"];   
        }
        if (isset($post["category"])) {
            $this->_category = $post["category"];   
        }
        if (isset($post["type"])) {
            $this->_type = $post["type"];   
        }
        if (isset($post["postType"])) {
            $this->_postType = $post["postType"];   
        }
        if (isset($post["filesToBeAdded"])) {
            $this->_filesToBeAdded = $post["filesToBeAdded"];
        }
        if (isset($post["recipients"])) {
            $this->_recipients = $post["recipients"];
        }

    }

    /**
     * Save any unadded files to the grid
     * 
     * @param MongoGridFS $grid Grid FS Object
     * 
     * @return boolean True of Success
     */
    public function saveFiles(\MongoGridFS $grid)
    {   
        foreach ($this->_filesToBeAdded as $name => $attributes) {
            $this->_files[] = $grid->storeFile($name, $attributes);
        }
    }

    /**
     * Get the Ids of files in the post
     * 
     * @return array List of Files
     */
    public function getFiles()
    {
        return $this->_files;
    }

    /**
     * Get the Id of the Parent of the Post
     *  
     * @return string Id of Parent
     */
    public function parent()
    {
        return $this->_parent;
    }

    /**
     * Get the Id of the Posr
     * 
     * @return string Id of the Post
     */
    public function id()
    {
        return $this->_id;
    }

    /**
     * Check if Post is a comment
     * 
     * @return boolean True if Post is comment
     */
    public function isComment()
    {
        if ($this->_postType=="comment")
            return true;
        return false;
    }

    /**
     * Add a Comment to the Post
     * 
     * @param void $id Id of the Post that is the comment
     *
     * @return void
     */
    public function addComment($id)
    {
        $this->_comments[] = $id;
    }

    /**
     * Get the text content of the post
     * 
     * @return string Text content of the post
     */
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * Get a Document for the Object
     * 
     * @return array Document for Post
     */
    public function document()
    {
        $document = array(
            "id" => $this->_id,
            "userId"=>$this->_userId,
            "content"=>$this->_content,
            "datetime"=>$this->_datetime,
            "parent"=>$this->_parent,
            "files"=>$this->_files,
            "category"=>$this->_category,
            "comments"=>$this->_comments,
            "type"=>$this->_type,
            "postType"=>$this->_postType,
            "likes"=>$this->_likes,
            "recipients"=>$this->_recipients
           
        );

        return $document;
    }

    /**
     * Update the Document for the Post
     * 
     * @param MongoCollection $collection Collection of Posts
     * 
     * @return void
     */
    public function update(\MongoCollection $collection)
    {
        $collection->update(
            array("id"=>$this->_id),
            $this->document()
        );
    }

    /**
     * Set Recipients for the Post
     * 
     * @param array $recipients List of recipient ids
     *
     * @return void
     */
    public function setRecipients(array $recipients)
    {
        $this->_recipients = $recipients;
    }

    /**
     * Get the Recipients of the Post
     * 
     * @return array List of Recipient Ids
     */
    public function getRecipients()
    {
        return $this->_recipients;
    }

    /**
     * Get the User Id of the Owner of the Post
     * 
     * @return string Id of the User of the Post
     */
    public function getUserId()
    {
        return $this->_userId;
    }

    /**
     * Like user post
     *   
     * @param string $likedUserId  Id of post liked user  
     * @param MongoCollection $postCollection Collections of posts 
     * @param MongoCollection $userCollection Collections of user
     * 
     * @return bool True if success
     */
    public function likePost($likedUserId, \MongoCollection $postCollection, \MongoCollection $userCollection)
    {   
        if (in_array($likedUserId, $this->_likes)) {    
            throw new Exceptions\Post\AlreadyLikesThisPostException("User with ID $likedUserId is already being liked this post");
        } else {
            User::addLikedPosts($likedUserId, $this->_id, $userCollection);
            $this->_likes[] = $likedUserId;
            $this->update($postCollection);
            return true;
        }
    }

    /**
     * Like user count
     * Get the Like Count
     * 
     * @return bool True if success
     */
    public function likesCount()
    {
        return count($this->_likes);
    }
    
    /**
     * Get the Liked Users Id
     * 
     * @return Array List of Liked Users
     */
    public function likedUsers()
    {
        return $this->_likes;
    }

    /**
     * Comment Count
     * Get the User Id of the Owner of the Post
     * 
     * @return bool True if success
     */
    public function commentCount()
    {
        return count($this->_comments);
    }

    /**
     * Comment Ids
     * Get the Comment Ids 
     * 
     * @return Array of Comment 
     */
    public function commentIds()
    {
        return $this->_comments;
    }

    /**
     * getComment 
     * Get the Comment details
     * 
     * @param MongoCollection $postCollection contain post collection
     * 
     * @return Array of Comments 
     */
    public function getComments(\MongoCollection $postCollection)
    {
        $comments = array();
        $commentCount = count($this->_comments);
        foreach ($this->_comments as $commentId) {
            $comment = Post::fromID($commentId, $postCollection);
            $comments[] = $comment->document();
        }
        return $comments;
    }
}
?>