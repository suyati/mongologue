<?php
/**
 * File Containing the Category Class
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
 * Class Managing Category
 *
 * @category Mongologue
 * @package  Core
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  NONE http://suyati.com
 * @version  0.1.1
 * @link     http://suyati.com
 */

class Premadepost
{
    private $_id;
    private $_name;

    /**
     * Create a premadepost from Id
     * 
     * @param string          $premadepostId Id of premadepost
     * @param MongoCollection $collection Collection of premadepost
     * 
     * @return void
     */
    public static function fromID($premadepostId, \MongoCollection $collection)
    {
        $premadepost = $collection->findOne(array("id"=> $premadepostId));
        if($category)
            return new self($category);
        else
            throw new Exceptions\Premadepost\PremadepostNotFoundException("No Such Post");
    }

    /**
     * Register a User to the System.
     * 
     * @param premadepost        $premadepost   premadepost Object to be added
     * @param MongoCollection $collection Collection of Categories
     *
     * @throws DuplicateCategoryException If the category id is already added
     * 
     * @return boolean True if Insertion happens
     */
    public static function registerPremadepost(self $premadepost, \MongoCollection $collection)
    {
        $tempPremadepost = $collection->findOne(array("id"=> $premadepost->id()));

        if ($tempPremadepost) {
            throw new Exceptions\Premadepost\DuplicatePremadepostException("Premadepost Id already Added");
        } else {
            if($tempPremadepost=="")
            $collection->insert($premadepost->document());
        }

        return true;
    }

     /**
     * Create Category Object from Document
     * 
     * @param array $document Document of Category
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
     * @param array $premadepost Details of Category
     */
    public function __construct(array $premadepost)
    {
        $this->_id = $premadepost["id"];
        $this->_name = $premadepost["name"];

    }

     /**
     * Get the Name of the Category
     * 
     * @return void
     */
    function name()
    {
        return $this->_name;
    }

    /**
     * Get the Id of the Category
     * 
     * @return string id of Category
     */
    function id()
    {
        return $this->_id;
    }

    /**
     * Convert Category to Document
     * 
     * @return array Document of Category
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