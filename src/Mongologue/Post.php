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
    private $_datetime;
    private $_category;

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
     * Constructor of Class
     * 
     * @param array           $post     Details of Post
     * @param MongoCollection $counters Collection of Counters
     */
    public function __construct(array $post, \MongoCollection $counters)
    {
        $this->_userId = $post["userId"];
        //$this->_datetime = $post["datetime"];
        $this->_content = $post["content"];

        $this->_counters = $counters;

        if (isset($post["id"])) {
            $this->_id = $post["id"];
        } else {
            $this->_id = self::getNextPostId($this->_counters);
        }
        if(isset($post["files"])){
        	$this->_files = $post["files"];
        }
        if(isset($post["parent"])){
    		$this->_parent= $post["parent"];	
    	}
    	if(isset($post["likes"])){
        	$this->_likes = $post["likes"];	
        }
        if(isset($post["comments"])){
        	$this->_comments = $post["comments"];	
        }
        if(isset($post["category"])){
        	$this->_comments = $post["category"];	
        }
        if(isset($post["type"])){
        	$this->_comments = $post["type"];	
        }
        if(isset($post["datetime"])){
        	$this->_datetime = $post["datetime"];
        }

    }

    /**
     * Create Post
     *
     * @param array $post Details Post to be Created
     *
     * @access public
     * @return bool True if success
     */
    /*public function createPost(array $post)
    {
        $post = new Post($post, $this->_countersCollection);
        return Post::savePost($post, $this->_grid, $this->_postCollection);
    }
*/
    public static function savePost(self $post, \MongoCollection $collection)
    {
    	$tempPost = $collection->findOne(array("id"=> $post->id()));
        if ($tempPost) {
            throw new Exceptions\Post\DuplicatePostException("Post Id already Added");
        } else {
            
            $collection->insert($post->document());
        }

        return true;
    }


}
?>