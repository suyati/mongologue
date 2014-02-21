<?php
/**
 * File Containing the Groups Class
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
            throw new \Exception("No Such Group");
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
            "name" => $this->_name
        );

        return $document;
    }

}
?>