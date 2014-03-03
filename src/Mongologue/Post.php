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
        $count = $counters.findAndModify(
            array("id"=>"posts"),
            array("$inc"=>array("s"=>1)),
            null,
            array("upsert"=>true, "new"=>true)
        );

        return $count->s;
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
        $this->_datetime = $post["datetime"];
        $this->_content = $post["content"];

        $this->_counters = $counters;

        if (isset($post["id"])) {
            $this->_id = $post["id"];
        } else {
            $this->_id = self::getNextPostId($this->_counters);
        }

    }
}
?>