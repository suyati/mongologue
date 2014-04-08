<?php
/**
 * File Containing the Message Model Class
 *
 * @category Mongologue
 * @package  Models
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://suyati.com
 * @link     http://github.com/xait/docxwriter
 */
namespace Mongologue\Models;

use Mongologue\Interfaces\Model;

/**
 * Class Managing the Message Properties
 *
 * @category Mongologue
 * @package  Models
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://suyati.com
 * @link     http://github.com/xait/docxwriter
 */
class Message extends Model
{
    protected $post;
    protected $to;
    protected $from;
    protected $user;
    protected $content;
    protected $parent;
    protected $likes;
    protected $type;
    protected $comments;
    protected $category;
    protected $files = array();
    protected $sent;

    private $_necessaryAttributes = array();

    /**
     * Create a Message Model
     * 
     * @param  Post     $post     Post Model
     * @param  User     $user     User Model for the owner
     * @param  Category $category Category Model
     * 
     * @return Message Message Model Object
     */
    public static function create(Post $post, User $user, Category $category = null)
    {
        $document = $post->document();
        
        $document["post"] = $document["id"];
        $document["user"] = $user->document();
        $document["from"] = $user->id;
        
        $document["likes"] = count($document["likes"]);
        $document["comments"] = count($document["comments"]);

        if ($category) {
            $document["category"] = $category->document();
        }

        $document["sent"] = $document["datetime"];

        return new self($document);
    }

    /**
     * Constructor of the Class
     * 
     * @param array $message Message Data
     */
    public function __construct($message)
    {
        parent::__construct($message);
    }

    /**
     * Set a Recipient for the message
     * 
     * @param mixed $id Id of the Recipient
     *
     * @return void
     */
    public function setRecipient($id)
    {
        $this->to = $id;
    }

    /**
     * Get the Necessary Attributes for the Model
     * 
     * @return array List of necessary attributes
     */
    public function necessaryAttributes()
    {
        return $this->_necessaryAttributes;
    }
}
