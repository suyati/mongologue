<?php
/**
 * File containing the Post Model Class
 *
 * @category Mongologue
 * @package  Models
 * @author   @tkannippoyil <tkannippoyil@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
namespace Mongologue\Models;

use \Mongologue\Interfaces\Model;
use \Mongologue\Exceptions\Post as Exceptions;
use \Mongologue\Exception;

/**
 * File containing the Post Model Class
 *
 * @category Mongologue
 * @package  Models
 * @author   @tkannippoyil <tkannippoyil@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
class Post extends Model
{
    protected $id;
    protected $content;
    protected $userId;
    protected $files = array();
    protected $parent;
    protected $likes = array();
    protected $comments = array();
    protected $type;
    protected $datetime;
    protected $category;
    protected $recipients = array();

    private $_filesToBeAdded = array();

    /**
     * Additional Data container
     * @var array
     */
    protected $data = array();

    private $_necessaryAttributes = array("content", "userId", "datetime");

    /**
     * Constructor of the Class
     * 
     * @param array $post Post Data
     */
    public function __construct($post)
    {
        parent::__construct($post);

        if (isset($post["filesToBeAdded"])) {
            $this->_filesToBeAdded = $post["filesToBeAdded"];
        }
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
        $this->recipients = $recipients;
    }

    /**
     * Add a File to the Post
     * 
     * @param mixed $file File to be Added
     *
     * @return void
     */
    public function addFile($file)
    {
        $this->files[] = $file;
    }

    /**
     * Set ID of the Post
     * 
     * @param string $id Id of the Post
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Add likes to the Post
     * 
     * @param string $userId Liked user's id
     *
     * @return void
     */
    public function addLikes($userId)
    {
        $this->likes[] = $userId;
    }

    /**
     * Check if Post is a comment
     * 
     * @return boolean True if Post is comment
     */
    public function isComment()
    {
        if ($this->type=="comment") {
            return true;
        }
        return false;
    }

    /**
     * Get the Files to be Added
     * 
     * @return array List of Files to be added
     */
    public function filesToBeAdded()
    {
        return $this->_filesToBeAdded;
    }

    /**
     * Add a Comment to the Post
     * 
     * @param string $postId Id of the Comment post
     *
     * @return void
     */
    public function addComment($postId)
    {
        if (in_array($postId, $this->comments)) {
            throw new Exception("Comment Already Exists");
        }

        $this->comments[] = $postId;
    }

    /**
     * Get the Necessary Attributes for the Model
     * 
     * @return array List of necessary Attributes of the Model
     */
    public function necessaryAttributes()
    {
        return $this->_necessaryAttributes;
    }
}
