<?php
/**
 * File Containing the Premadepost Class
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
 * Class Managing Premadepost
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
     * @param MongoCollection $collection    Collection of premadepost
     * 
     * @return void
     */
    public static function fromID($premadepostId, \MongoCollection $collection)
    {
        $premadepost = $collection->findOne(array("id"=> $premadepostId));
        if($premadepost)
            return new self($premadepost);
        else
            throw new Exceptions\Premadepost\PremadepostNotFoundException("No Such Post");
    }

    /**
     * Register a User to the System.
     * 
     * @param premadepost     $premadepost premadepost Object to be added
     * @param MongoCollection $collection  Collection of Categories
     *
     * @throws DuplicatePremadepostException If the premadepost id is already added
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
     * Create Premadepost Object from Document
     * 
     * @param array $document Document of Premadepost
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
     * @param array $premadepost Details of Premadepost
     */
    public function __construct(array $premadepost)
    {
        $this->_id = $premadepost["id"];
        $this->_name = $premadepost["name"];

    }


    /**
     * Update a Premadepost to the System.
     * 
     * @param Premadepost     $premadepost           Premadepost Object to be added
     * @param MongoCollection $premadepostCollection Collection of Premadeposts
     *
     * @throws PremadepostNotFoundException If the premadepost id not found
     * 
     * @return boolean True if Update happens
     */
    public static function updatePremadepost(self $premadepost, \MongoCollection $premadepostCollection)
    {
        $tempPremadepost = Premadepost::fromID($premadepost->id(), $premadepostCollection);
        if (!empty($tempPremadepost)) {
            $tempPremadepost->_name = $premadepost->name();
            $tempPremadepost->update($premadepostCollection);
        } else {
            throw new Exceptions\Premadepost\PremadepostNotFoundException("Premadepost Not Found");
        }

        return true;
    }


    /**
     * Update the Document for the Premadepost
     * 
     * @param MongoCollection $collection Collection of Premadeposts
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
     * Remove a Premadepost to the System.
     * 
     * @param string          $premadepostId         Premadepost Object to be added
     * @param MongoCollection $premadepostCollection Collection of Premadeposts
     *
     * @throws PremadepostNotFoundException If the premadepost id not found
     * 
     * @return boolean True if Update happens
     */
    public static function removePremadepost($premadepostId, \MongoCollection $premadepostCollection)
    {
        $tempPremadepost = Premadepost::fromID($premadepostId, $premadepostCollection);
        if (!empty($tempPremadepost)) {
            $premadepostCollection->remove(array("id" => $tempPremadepost->id()), array("justOne" => true));
        } else {
            throw new Exceptions\Premadepost\PremadepostNotFoundException("Premadepost Not Found");
        }

        return true;
    }

     /**
     * Get the Name of the Premadepost
     * 
     * @return void
     */
    function name()
    {
        return $this->_name;
    }

    /**
     * Get the Id of the Premadepost
     * 
     * @return string id of Premadepost
     */
    function id()
    {
        return $this->_id;
    }

    /**
     * Convert Premadepost to Document
     * 
     * @return array Document of Premadepost
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