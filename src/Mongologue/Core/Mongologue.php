<?php
/**
 * File Containing the Mongolouge Core Class
 *
 * @category Mongolouge
 * @package  Core
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
namespace Mongologue\Core;

use \Mongologue\Collection;

/**
 * Mongolouge Core Class
 *
 * @todo Implement Posts and Inbox
 * 
 * @category Mongolouge
 * @package  Core
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
class Mongologue
{
    private $_userCollection;
    private $_groupCollection;
    private $_postCollection;
    private $_inboxCollection;
    private $_categoryCollection;
    private $_notificationCollection;

    /**
     * Constructor of the Class
     * 
     * @param Collection\User     $userCollection     [description]
     * @param Collection\Group    $groupCollection    [description]
     * @param Collection\Post     $postCollection     [description]
     * @param Collection\Inbox    $inboxCollection    [description]
     * @param Collection\Category $categoryCollection [description]
     */
    public function __construct(Collection\User $userCollection, Collection\Group $groupCollection, Collection\Post $postCollection, Collection\Inbox $inboxCollection, Collection\Category $categoryCollection, Collection\Notification $notificationCollection)
    {
        $this->_userCollection         = $userCollection;
        $this->_groupCollection        = $groupCollection;
        $this->_postCollection         = $postCollection;
        $this->_inboxCollection        = $inboxCollection;
        $this->_categoryCollection     = $categoryCollection;
        $this->_notificationCollection = $notificationCollection;
    }

    /**
     * Allows Executing Functions on Group Collections
     * 
     * @param mixed $callable Executable Command for the Collection
     * 
     * @return mixed Result of Executed Command
     */
    public function group($callable)
    {
        $params = array_slice(func_get_args(), 1);
        return $this->_groupCollection->execute($callable, $params);
    }

    /**
     * Allows Executing Functions on User Collections
     * 
     * @param mixed $callable Executable Command for the Collection
     * 
     * @return mixed Result of Executed Command
     */
    public function user($callable)
    {
        $params = array_slice(func_get_args(), 1);
        return $this->_userCollection->execute($callable, $params);
    }

    /**
     * Allows Executing Functions on Post Collections
     * 
     * @param mixed $callable Executable Command for the Collection
     * 
     * @return mixed Result of Executed Command
     */
    public function post($callable)
    {
        $params = array_slice(func_get_args(), 1);
        return $this->_postCollection->execute($callable, $params);
    }

    /**
     * Allows Executing Functions on Inbox Collections
     * 
     * @param mixed $callable Executable Command for the Collection
     * 
     * @return mixed Result of Executed Command
     */
    public function inbox($callable)
    {
        $params = array_slice(func_get_args(), 1);
        return $this->_inboxCollection->execute($callable, $params);
    }

    /**
     * Allows Executing Functions on Category Collections
     * 
     * @param mixed $callable Executable Command for the Collection
     * 
     * @return mixed Result of Executed Command
     */
    public function category($callable)
    {
        $params = array_slice(func_get_args(), 1);
        return $this->_categoryCollection->execute($callable, $params);
    }

    /**
     * Allows Executing Functions on Notification Collections
     * 
     * @param mixed $callable Executable Command for the Collection
     * 
     * @return mixed Result of Executed Command
     */
    public function notification($callable)
    {
        $params = array_slice(func_get_args(), 1);
        return $this->_notificationCollection->execute($callable, $params);
    }
}
