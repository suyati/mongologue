<?php
/**
 * File containing the Post Model Class
 *
 * @category Mongologue
 * @package  Models
 * @author   @tkannippoyil <tkannippoyil@suyati.com>
 * @license  none http://suyati.com
 * @link     http://github.com/xait/docxwriter
 */
namespace Mongologue\Models;

use \Mongologue\Interfaces\Model;
use \Mongologue\Exceptions\Post as Exceptions;

/**
 * File containing the Post Model Class
 *
 * @category Mongologue
 * @package  Models
 * @author   @tkannippoyil <tkannippoyil@suyati.com>
 * @license  none http://suyati.com
 * @link     http://github.com/xait/docxwriter
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
    protected $postType;
    protected $datetime;
    protected $category;
    protected $timer;
    protected $recipients = array();

    protected $filesToBeAdded = array();

    /**
     * Additional Data container
     * @var array
     */
    protected $data = array();

    private $_necessaryAttributes = array("id", "content", "userId", "datetime");

    /**
     * Constructor of the Class
     * 
     * @param array $post Post Data
     */
    public function __construct($post)
    {
        parent::__construct($post);
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
     * Get the Necessary Attributes for the Model
     * 
     * @return array List of necessary Attributes of the Model
     */
    public function necessaryAttributes()
    {
        return $this->_necessaryAttributes;
    }
}