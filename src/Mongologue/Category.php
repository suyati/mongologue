<?php
/**
 * File Containing the Category Class
 *
 * @category Mongologue
 * @package  Core
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @author   @naveenbos <nmohanan@suyati.com>
 * @license  NONE http://github.com/suyati/mongologue
 * @version  0.1.1
 * @link     http://github.com/suyati/mongologue
 */
namespace Mongologue;

/**
 * Class Managing Category
 *
 * @category Mongologue
 * @package  Core
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  NONE http://github.com/suyati/mongologue
 * @version  0.1.1
 * @link     http://github.com/suyati/mongologue
 */

class Category
{
    private $_id;
    private $_name;

    /**
     * Create a Category from Id
     * 
     * @param string          $categoryId Id of Category
     * @param MongoCollection $collection Collection of Category
     * 
     * @return void
     */
    public static function fromID($categoryId, \MongoCollection $collection)
    {
        $category = $collection->findOne(array("id"=> $categoryId));
        if($category)
            return new self($category);
        else
            throw new Exceptions\Category\CategoryNotFoundException("No Such Category");
    }

    /**
     * Register a User to the System.
     * 
     * @param Category        $category   Category Object to be added
     * @param MongoCollection $collection Collection of Categories
     *
     * @throws DuplicateCategoryException If the category id is already added
     * 
     * @return boolean True if Insertion happens
     */
    public static function registerCategory(self $category, \MongoCollection $collection)
    {
        $tempCategory = $collection->findOne(array("id"=> $category->id()));

        if ($tempCategory) {
            throw new Exceptions\Category\DuplicateCategoryException("Category Id already Added");
        } else {
            if($tempCategory=="")
            $collection->insert($category->document());
        }

        return true;
    }

    /**
     * Update a Category to the System.
     * 
     * @param Category        $category           Category Object to be added
     * @param MongoCollection $categoryCollection Collection of Categorys
     *
     * @throws CategoryNotFoundException If the category id not found
     * 
     * @return boolean True if Update happens
     */
    public static function updateCategory(self $category, \MongoCollection $categoryCollection)
    {
        $tempCategory = Category::fromID($category->id(), $categoryCollection);
        if (!empty($tempCategory)) {
            $tempCategory->_name = $category->name();
            $tempCategory->update($categoryCollection);
        } else {
            throw new Exceptions\Category\CategoryNotFoundException("Category Not Found");
        }

        return true;
    }

    /**
     * Remove a Category to the System.
     * 
     * @param string          $categoryId         Category Object to be added
     * @param MongoCollection $categoryCollection Collection of Categorys
     *
     * @throws CategoryNotFoundException If the category id not found
     * 
     * @return boolean True if Update happens
     */
    public static function removeCategory($categoryId, \MongoCollection $categoryCollection)
    {
        $tempCategory = Category::fromID($categoryId, $categoryCollection);
        if (!empty($tempCategory)) {
            $categoryCollection->remove(array("id" => $tempCategory->id()), array("justOne" => true));
        } else {
            throw new Exceptions\Category\CategoryNotFoundException("Category Not Found");
        }

        return true;
    }

    /**
     * Update the Document for the Category
     * 
     * @param MongoCollection $collection Collection of Categorys
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
     * @param array $category Details of Category
     */
    public function __construct(array $category)
    {
        $this->_id = $category["id"];
        $this->_name = $category["name"];

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