<?php
/**
 * File Contining the User Model Class
 *
 * @category Mongologue
 * @package  Models
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
namespace Mongologue\Models;

use \Mongologue\Interfaces\Model;
use \Mongologue\Exception;

use \Mongologue\Exceptions\User as Exceptions;

/**
 * File Contining the User Model Class
 *
 * @category Mongologue
 * @package  Models
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
class User extends Model
{
    protected $id;
    protected $handle;
    protected $firstName;
    protected $lastName;
    protected $email;
    protected $pic;

    protected $following = array();
    protected $followers = array();
    protected $groups = array();
    protected $blocking = array();
    protected $followingGroups = array();
    protected $likes = array();

    /**
     * Allows Storage of other Information
     * @var array;
     */
    protected $data = array();

    private $_necessaryAttributes = array("id", "handle", "firstName", "lastName", "email");

    /**
     * Constructor of Class
     * 
     * @param array $user User Data
     */
    public function __construct(array $user)
    {
        parent::__construct($user);
    }

    /**
     * Follow a User
     * 
     * @param string $followeeId Id of User to Follow
     * 
     * @return void
     */
    public function follow($followeeId)
    {
        if (in_array($followeeId, $this->following)) {
            throw new Exceptions\AlreadyFollowingException("This User is already Being Followed");
        }

        $this->following[] = $followeeId;
    }

    /**
     * Unfollow a User
     * 
     * @param string $followeeId Id of the User to unfollow
     * 
     * @return void
     */
    public function unfollow($followeeId)
    {
        if (!in_array($followeeId, $this->following)) {
            throw new Exceptions\NotFollowingException("No Such User is being Followed");
        }

        $this->following = array_diff($this->following, array($followeeId));
    }

    /**
     * Follow a Group
     * 
     * @param string $groupId Id of Group to Follow
     * 
     * @return void
     */
    public function followGroup($groupId)
    {
        if (in_array($groupId, $this->followingGroups)) {
            throw new Exceptions\AlreadyFollowingException("This Group $groupId is already Being Followed");
        }

        $this->followingGroups[] = $groupId;

    }

    /**
     * Unfollow a Group
     * 
     * @param string $groupId Id of the Group to unfollow
     * 
     * @return void
     */
    public function unfollowGroup($groupId)
    {
        if (!in_array($groupId, $this->followingGroups)) {
            throw new Exceptions\NotFollowingException("No Such User is being Followed");
        }

        $this->followingGroups = array_diff($this->followingGroups, array($groupId));
    }

    /**
     * Add a Follower
     * 
     * @param string $followerId Id of the Follower
     *
     * @return void
     */
    public function addFollower($followerId)
    {
        if (in_array($followerId, $this->followers)) {
            throw new Exceptions\AlreadyFollowingException("This User is already Following");
        }

        $this->followers[] = $followerId;
    }

    /**
     * Remove a User from the Followers List
     * 
     * @param string $followerId Id of the Follower to be removed
     * 
     * @return void
     */
    public function removeFollower($followerId)
    {
        if (!in_array($followerId, $this->followers)) {
            throw new Exceptions\FollowerNotFoundException("No Such Follower");
        }

        $this->followers = array_diff($this->followers, array($followerId));
    }

    /**
     * Join a Group
     * 
     * @param string $groupId Id of the Group
     *
     * @todo implement exception
     * 
     * @return void
     */
    public function joinGroup($groupId)
    {
        if (in_array($groupId, $this->groups)) {
            throw new Exception("Already a Member");
        }

        $this->groups[] = $groupId;
    }

    /**
     * Leave a Group
     * 
     * @param string $groupId Id of the group
     *
     * @todo implement exception
     * 
     * @return void
     */
    public function leaveGroup($groupId)
    {
        if (!in_array($groupId, $this->groups)) {
            throw new Exception('Not a Member of Group');
        }

        $this->groups = array_diff($this->groups, array($groupId));
    }

    /**
     * Setting value to the liked posts
     * 
     * @param string $val id of the posts
     * 
     * @return void      
     */
    public function setlikes($val)
    {
        $this->likes[] = $val;
    }

    /**
     * Get the Necessary Attributes of the Model
     * 
     * @return array Necessary Attributes of the Model
     */
    public function necessaryAttributes()
    {
        return $this->_necessaryAttributes;
    }
}
