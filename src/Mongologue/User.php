<?php
/**
 * File Containing the User Class
 *
 * @category Mongologue
 * @package  Core
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  NONE http://suyati.com
 * @version  0.1.1
 * @link     http://suyati.com
 */
namespace Mongologue;

use \Mongologue\Group;
use \Mongologue\Exceptions;

/**
 * Class Managing Users
 *
 * @category Mongologue
 * @package  Core
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  NONE http://suyati.com
 * @version  0.1.1
 * @link     http://suyati.com
 */
class User
{
    private $_id;
    private $_handle;
    private $_firstName;
    private $_lastName;
    private $_emailId;
    private $_profilePicUrl;
    private $_age;
    private $_friends = array();
    private $_likePages = array();
    private $_followers = array();
    private $_followingUsers = array();
    private $_groups = array();
    private $_followingGroups = array();

    /**
     * Create a User Instance from Documetn
     * 
     * @param string $document JSON string for User
     * 
     * @return User Instance of a User
     */
    public static function fromDocument($document)
    {
        return new self($document);
    }

    /**
     * Get a User from an Id
     * 
     * @param string          $userId     Id of the USer
     * @param MongoCollection $collection user collection
     *
     * @throws UserNotFoundException if the user with the provided id does not exist
     *                               in the colleciton.
     *                               
     * @return User Instance of a User
     */
    public static function fromID($userId,\MongoCollection $collection)
    {
        $user = $collection->findOne(array("id"=> $userId));
        if($user)
            return new self($user);
        else
            throw new Exceptions\User\UserNotFoundException("No Such User");
            
    }

    /**
     * Register a User to the System.
     * 
     * @param User            $user       User Object to be registered
     * @param MongoCollection $collection Collection of Users
     *
     * @throws DuplicateUserException If the user id is already registered
     * 
     * @return boolean True if Insertion happens
     */
    public static function registerUser(self $user, \MongoCollection $collection)
    {
        $tempUser = $collection->findOne(array("id"=> $user->id()));

        if ($tempUser) {
            throw new Exceptions\User\DuplicateUserException("User Id already Registered");
        } else {
            $collection->insert($user->document());
        }

        return true;
    }
    
    /**
     * Constructor of User
     * 
     * @param array $user Details of User
     */
    public function __construct(array $user)
    {
        $this->_id = $user["id"];
        $this->_handle = $user["handle"];
        $this->_firstName = $user["firstName"];
        $this->_lastName = $user["lastName"];

        if (isset($user["emailId"])) {
            $this->_emailId = $user["emailId"];
        }
        if (isset($user["profilePicUrl"])) {
            $this->_profilePicUrl = $user["profilePicUrl"];
        }
        if (isset($user["age"])) {
            $this->_age = $user["age"];
        }
        if (isset($user["friends"])) {
            $this->_friends = $user["friends"];
        }
        if (isset($user["likePages"])) {
            $this->_likePages = $user["likePages"];
        }
        if (isset($user["groups"])) {
            $this->_groups = $user["groups"];
        }
        if (isset($user["followers"])) {
            $this->_followers = $user["followers"];
        }
        if (isset($user["followingUsers"])) {
            $this->_followingUsers = $user["followingUsers"];
        }
        if (isset($user["followingGroups"])) {
            $this->_followingGroups = $user["followingGroups"];
        }
    }

    /**
     * Return a JSON Document for the User
     * 
     * @return mixed Json String for the User
     */
    public function document()
    {
        $document = array(
            "id"        => $this->_id,
            "handle"    => $this->_handle,
            "firstName" => $this->_firstName,
            "lastName"  => $this->_lastName,
            "emailId"   =>  $this->_emailId,
            "profilePicUrl"   =>  $this->_profilePicUrl,
            "age"   =>  $this->_age,
            "friends"   =>  $this->_friends,
            "likePages"   =>  $this->_likePages,
            "groups" => $this->_groups,
            "followers" => $this->_followers,
            "followingGroups" => $this->_followingGroups,
            "followingUsers" => $this->_followingUsers
        );

        return $document;

    }

    /**
     * Follow this USer
     *   
     * @param string          $userId     Id of the Following User
     * @param MongoCollection $collection Collection of Users 
     * 
     * @return bool True if success
     */
    public function followUser($userId, \MongoCollection $collection)
    {
        if (in_array($userId, $this->_followingUsers)) {
            throw new Exceptions\User\AlreadyFollowingException("User with ID $userId is already being followed");
        } else {
            $user = self::fromID($userId, $collection);
            $user->addFollower($this->_id, $collection);
            $this->_followingUsers[] = $userId;
            $this->update($collection);
            return true;
        }
    }

    /**
     * UnFollow this USer
     *   
     * @param string          $userId     Id of the Following User
     * @param MongoCollection $collection Collection of Users 
     * 
     * @return bool True if success
     */
    public function unFollowUser($userId, \MongoCollection $collection)
    {
        if (in_array($userId, $this->_followingUsers)) {
            $this->_followingUsers = array_diff($this->_followingUsers, array($userId));
            $user = self::fromID($userId, $collection);
            $user->removeFollower($this->_id, $collection);
            $this->update($collection);
            return true;
        } else {
            throw new Exceptions\User\NotFollowingException("User is not following User With ID $userId");
        }
    }

    /**
     * remove a Follower
     * 
     * @param string          $userId     Remove a Follower
     * @param MongoCollection $collection Collection of Users
     *
     * @return  boolean True if Success
     */
    public function removeFollower($userId, \MongoCollection $collection)
    {
        if (in_array($userId, $this->_followers)) {
            $this->_followers = array_diff($this->_followers, array($userId));
            $this->update($collection);
            return true;
        } else {
            throw new Exceptions\User\FollowerNotFoundException("Follower with ID $userId cannot be found in followers list");
        }
    }

    /**
     * Follow a Group
     * 
     * @param string          $groupId         Id of Group to Follow
     * @param MongoCollection $userCollection  Collection of Users
     * @param MongoCollection $groupCollection Collection of Groups
     * 
     * @return boolean True if Success
     */
    public function followGroup($groupId, \MongoCollection $userCollection, \MongoCollection $groupCollection)
    {
        if (in_array($groupId, $this->_followingGroups)) {
            throw new Exceptions\Group\GroupAlreadyFollowingException("Group with ID $groupId is already being followed");
        } else {
            $group = Group::fromID($groupId, $groupCollection);        
            $group->addFollower($this->_id, $groupCollection);
            $this->_followingGroups[] = $groupId;
            $this->update($userCollection);
            return true;
        }
    }

    /**
     * UnFollow a Group
     * 
     * @param string          $groupId         Id of Group to Follow
     * @param MongoCollection $userCollection  Collection of Users
     * @param MongoCollection $groupCollection Collection of Groups
     * 
     * @return boolean True if Success
     */
    public function unFollowGroup($groupId, \MongoCollection $userCollection, \MongoCollection $groupCollection)
    {
        if (in_array($groupId, $this->_followingGroups)) {
            $this->_followingGroups = array_diff($this->_followingGroups, array($groupId));
            $group = Group::fromID($groupId, $groupCollection);            
            $group->removeFollower($this->_id, $groupCollection);
            $this->update($userCollection);
            return true;
        } else {
            throw new Exceptions\Group\UserNotFollowingThisGroupException("User not following this groups");
        }
    }

    /**
     * Join a Group
     * 
     * @param string          $groupId         Id of Group to Join
     * @param MongoCollection $userCollection  Collection of Users
     * @param MongoCollection $groupCollection Collection of Groups
     * 
     * @return boolean True if Success
     */
    public function joinGroup($groupId, \MongoCollection $userCollection, \MongoCollection $groupCollection)
    {
        if (in_array($groupId, $this->_groups)) {
            throw new Exceptions\Group\UserAlreadyMemberException("User is already a member of this group");
        } else {
            $group = Group::fromID($groupId, $groupCollection);        
            $group->addMember($this->_id, $groupCollection);
            $this->_groups[] = $groupId;
            $this->update($userCollection);
            return true;
        }
    }

    /**
     * Leave a Group
     * 
     * @param string          $groupId         Id of Group to Leave
     * @param MongoCollection $userCollection  Collection of Users
     * @param MongoCollection $groupCollection Collection of Groups
     * 
     * @return boolean True if Success
     */
    public function leaveGroup($groupId, \MongoCollection $userCollection, \MongoCollection $groupCollection)
    {
        if (in_array($groupId, $this->_groups)) {

            $this->_groups = array_diff($this->_groups, array($groupId));
            $group = Group::fromID($groupId, $groupCollection);            
            $group->removeMember($this->_id, $groupCollection);
            $this->update($userCollection);
            return true;
        } else {
            throw new Exceptions\Group\UserNotMemberOfGroup("User is not a member of this groups");
        }
    }

    /**
     * Add a Follower
     * 
     * @param string          $userId     Add a Follower
     * @param MongoCollection $collection Collection of Users
     *
     * @return  boolean True if Success
     */
    public function addFollower($userId, \MongoCollection $collection)
    {

        $user = self::fromID($userId, $collection);
       
        $this->_followers[] = $userId;
        $this->update($collection);
        return true;
    }

    /**
     * Update the Document for the User
     * 
     * @param MongoCollection $collection Collection of Users
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
     * Get the Full NAme of the USer
     * 
     * @return string Full name of the USer
     */
    public function name()
    {
        return implode(' ', array($this->_firstName, $this->_lastName));
    }

    /**
     * Get the Email of the User
     * 
     * @return string email id of the user
     */
    public function email()
    {
        return $this->_emailId;
    }

    /**
     * Get the Id of the User
     *
     * @return string Id of the User
     */
    public function id()
    {
        return $this->_id;
    }

    /**
     * Return the Followers of the User
     * 
     * @return array Id of the Followers of the USer
     */
    public function followers()
    {
        return $this->_followers;
    }

    /**
     * Get the groups of which the user is a member
     * 
     * @return array List of group ids
     */
    public function groups()
    {
        return $this->_groups;
    }

    /**
     * get the Users the USer is following
     * 
     * @return array list of users that the user is following
     */
    public function followingUsers()
    {
        return $this->_followingUsers;
    }

    /**
     * Get the Groups the User is following
     * 
     * @return array list of groups that the user is following
     */
    public function followingGroups()
    {
        return $this->_followingGroups;
    }
}
?>