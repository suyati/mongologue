<?php
/**
 * File Containing the Groups Class
 *
 * @category Mongologue
 * @package  Core
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @author   @naveenbos <nmohanan@suyati.com>
 * @license  NONE http://suyati.com
 * @version  0.1.1
 * @link     http://suyati.com
 */
namespace Mongologue;

/**
 * Class Managing Groups
 *
 * @todo  implement child groups
 *
 * @category Mongologue
 * @package  Core
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  NONE http://suyati.com
 * @version  0.1.1
 * @link     http://suyati.com
 */
class Group
{
    private $_id;
    private $_name;
    private $_type;
    private $_parent;
    private $_children = array();
    private $_members = array();
    private $_followers = array();

    /**
     * Create a Group from Id
     * 
     * @param string          $groupId    Id of Group
     * @param MongoCollection $collection Collection of Groups
     * 
     * @return void
     */
    public static function fromID($groupId, \MongoCollection $collection)
    {
        $group = $collection->findOne(array("id"=> $groupId));
        
        if($group)
            return new self($group);
        else
            throw new Exceptions\Group\GroupNotFoundException("No Such Group");
    }

    /**
     * Find a Group from Name and Parent ID
     * 
     * @param string          $groupName  Name of Group
     * @param MongoCollection $collection Group Collection
     * @param string          $parentId   Id of the parent group
     * 
     * @return string Id of the Group
     */
    public static function fromName($groupName, \MongoCollection $collection, $parentId=null)
    {
        $query = array("name"=>$groupName, "parent"=>$parentId);

        $group = $collection->findOne($query);

        if ($group) {
            $group = new self($group);
            return $group->id();
        } else {
            throw new Exceptions\Group\GroupNotFoundException("No Such Group");
        }

    }

     /**
     * Register a Group to the System.
     * 
     * @param Group           $group      Group Object to be added
     * @param MongoCollection $collection Collection of Groups
     *
     * @throws DuplicateGroupException If the group id is already added
     * 
     * @return boolean True if Insertion happens
     */
    public static function registerGroup(self $group, \MongoCollection $collection)
    {
        $tempGroup = $collection->findOne(array("id"=> $group->id()));

        if ($tempGroup) {
            throw new Exceptions\Group\DuplicateGroupException("Group Id already Added");
        } else {
            if($tempGroup=="")
            $collection->insert($group->document());
        }

        return true;
    }

    /**
     * Update a Group to the System.
     * 
     * @param Group           $group           Group Object to be added
     * @param MongoCollection $groupCollection Collection of Groups
     *
     * @throws GroupNotFoundException If the group id not found
     * 
     * @return boolean True if Update happens
     */
    public static function updateGroup(self $group, \MongoCollection $groupCollection)
    {
        $tempGroup = Group::fromID($group->id(), $groupCollection);
        if (!empty($tempGroup)) {
            $tempGroup->_name = $group->name();
            $tempGroup->update($groupCollection);
        } else {
            throw new Exceptions\Group\GroupNotFoundException("Group Not Found");
        }

        return true;
    }

    /**
     * Remove a Group to the System.
     * 
     * @param string          $groupId         Group Object to be added
     * @param MongoCollection $groupCollection Collection of Groups
     *
     * @throws GroupNotFoundException If the group id not found
     * 
     * @return boolean True if Update happens
     */
    public static function removeGroup($groupId, \MongoCollection $groupCollection)
    {
        $tempGroup = Group::fromID($groupId, $groupCollection);
        if (!empty($tempGroup)) {
            $groupCollection->remove(array("id" => $tempGroup->id()), array("justOne" => true));
        } else {
            throw new Exceptions\Group\GroupNotFoundException("Group Not Found");
        }

        return true;
    }

    /**
     * Create Group Object from Document
     * 
     * @param array $document Document of Group
     * 
     * @return void
     */
    public static function fromDocument($document)
    {
        return new self($document);
    }

    /**
     * Constructor of Class
     * 
     * @param array $group Details of Group
     */
    public function __construct(array $group)
    {
        $this->_id = $group["id"];
        $this->_name = $group["name"];

        if (isset($group["type"])) {
            $this->_type = $group["type"];
        }
        if (isset($group["parent"])) {
            $this->_parent = $group["parent"];
        }
        if (isset($group["members"])) {
            $this->_members = $group["members"];
        }
        if (isset($group["followers"])) {
            $this->_followers = $group["followers"];
        }
        if (isset($group["children"])) {
            $this->_children = $group["children"];
        }
    }

    /**
     * Get the Name of the Group
     * 
     * @return void
     */
    function name()
    {
        return $this->_name;
    }

    /**
     * Get the Id of the Group
     * 
     * @return string id of group
     */
    function id()
    {
        return $this->_id;
    }

    /**
     * Get the parent group ID
     * 
     * @return string Id of the Parent Group
     */
    public function parent()
    {
        return $this->_parent;
    }

    /**
     * Add a Follower
     * 
     * @param string          $userId     Add a Follower
     * @param MongoCollection $collection Collection of Groups
     *
     * @return  boolean True if Success
     */
    public function addFollower($userId, \MongoCollection $collection)
    {       
        $this->_followers[] = $userId;
        $this->update($collection);
        return true;
    }

    /**
     * Add a Member
     * 
     * @param string          $userId     Add a Memeber
     * @param MongoCollection $collection Collection of Groups
     *
     * @return  boolean True if Success
     */
    public function addMember($userId, \MongoCollection $collection)
    {       
        $this->_members[] = $userId;
        $this->update($collection);
        return true;
    }

    /**
     * remove a Follower
     * 
     * @param string          $userId     Remove a Follower
     * @param MongoCollection $collection Collection of Groups
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
            throw new Exceptions\Group\FollowerNotFoundException("Follower with ID $userId cannot be found in followers list");
        }
    }

    /**
     * remove a Member
     * 
     * @param string          $userId     Remove a Member
     * @param MongoCollection $collection Collection of Groups
     *
     * @return  boolean True if Success
     */
    public function removeMember($userId, \MongoCollection $collection)
    {
        if (in_array($userId, $this->_members)) {
            $this->_members = array_diff($this->_members, array($userId));
            $this->update($collection);
            return true;
        } else {
            throw new Exceptions\Group\MemberNotFoundException("Member with ID $userId cannot be found in member list");
        }
    }

    /**
     * Update the Document for the Group
     * 
     * @param MongoCollection $collection Collection of Groups
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
     * Get all Members of Group
     * 
     * @return array List of member ids
     */
    public function getMembers()
    {
        return $this->_members;
    }

    /**
     * Get all followers in the Group
     * 
     * @return array List of Follower ids
     */
    public function getFollowers()
    {
        return $this->_followers;
    }

    /**
     * Convert Group to Document
     * 
     * @return array Document of Group
     */
    public function document()
    {
        $document = array(
            "id" => $this->_id,
            "name" => $this->_name,
            "type" => $this->_type,
            "parent" => $this->_parent,
            "members" => $this->_members,
            "followers" => $this->_followers,
            "children" => $this->_children
        );

        return $document;
    }

}
?>