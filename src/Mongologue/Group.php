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
    private $_parent=array();

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
     * Register a User to the System.
     * 
     * @param Group            $group       Group Object to be added
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
            "parent" => $this->_parent
        );

        return $document;
    }

}
?>