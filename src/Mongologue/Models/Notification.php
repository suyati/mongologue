<?php
/**
 * File containing the Notification Model Class
 *
 * @category Mongologue
 * @package  Models
 * @author   @tkannippoyil <tkannippoyil@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
namespace Mongologue\Models;

use \Mongologue\Interfaces\Model;
use \Mongologue\Exception;

/**
 * File containing the Notification Model Class
 *
 * @category Mongologue
 * @package  Models
 * @author   @tkannippoyil <tkannippoyil@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
class Notification extends Model
{
    protected $id;
    protected $notifierId;
    protected $message;
    protected $userId;
    protected $userPic;
    protected $postId;
    protected $postImage;
    protected $type;
    protected $read;

    /**
     * Additional Data container
     * @var array
     */
    protected $data = array();

    private $_necessaryAttributes = array("notifierId", "message", "userId");

    /**
     * Constructor of the Class
     * 
     * @param array $notification Notification Data
     */
    public function __construct($notification)
    {
        parent::__construct($notification);
    }

    /**
     * Set ID of the Notification
     * 
     * @param string $id Id of the Notification
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
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
