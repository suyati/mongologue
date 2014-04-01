<?php
/**
 * File containing the Inbox Collection Class
 *
 * @category Mongologue
 * @package  Collection
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
namespace Mongologue\Collection;

use \Mongologue\Interfaces\Collection;
use \Mongologue\Core\Collections;
use \Mongologue\Models;

/**
 * Class Managing the Inbox Collection
 *
 * @category Mongologue
 * @package  Collection
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
class Inbox implements Collection
{
    private $_collection;
    private $_collections;

    /**
     * Constructor function
     *
     * @param MongoColleciton $mongoCollection Mongo Collection Object
     * @param Collections     $collections     Group of Collecitons
     */
    public function __construct(\MongoCollection $mongoCollection, Collections $collections)
    {
        $this->_collections = $collections; 
        $this->_collection = $mongoCollection;
    }

    /**
     * Write Messages to Recipient Inbox
     * 
     * @param Models\Post $post Post Models
     * 
     * @return boolean True if Success
     */
    public function writeToInbox(Models\Post $post)
    {
        $recipients = $post->recipients;

        foreach ($recipients as $recipient) {
            $message = $post->document();
            $message["recipient"] = $recipient;
            $this->_collection->insert($message);
        }

        return true;
    }

    /**
     * Get the Post Feed For a User
     * 
     * @param string  $userId ID of the User
     * @param integer $limit  Pagination Limit
     * @param integer $since  Since Which ID
     * 
     * @return array List of Posts in Feed
     */
    public function get($userId, $limit=null, $since=null)
    {
        $query = array("recipient" => $userId);
        if($since) 
            $query["id"] = array('$gt' => $since);

        $cursor = $cursor->sort(array("id"=>-1));

        if($limit)
            $cursor->limit((int)$limit);

        return iterator_to_array($cursor);
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