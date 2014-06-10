<?php
/**
 * File Containing the Category Collection Class
 *
 * @category Mongologue
 * @package  Collection
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
namespace Mongologue\Collection;

use \Mongologue\Exceptions as Exceptions;
use \Mongologue\Interfaces\Collection;
use \Mongologue\Models;
use \Mongologue\Core\Collections;

/**
 * Class Managing the Category Collection
 *
 * @category Mongologue
 * @package  Collection
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @license  none http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
class Category implements Collection
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
        $this->_collection  = $mongoCollection;
    }

    /**
     * Update a Category Document
     * 
     * @param Models\Category $category A Category Model
     *
     * @todo Handle Exception Cases
     * 
     * @return void
     */
    public function update(Models\Category $category)
    {
        $this->_collection->update(
            array("id" => $category->id),
            $category->document()
        );
    }

    /**
     * Get All Categories in the System
     * 
     * @return array List of Categories in the System
     */
    public function all()
    {
        $categories = array();
        $cursor     = $this->_collection->find();

        foreach ($cursor as $document) {
            $categories[] = new Models\Category($document);
        }

        return $categories;
    }

    /**
     * Get a Category Model from a Category Id
     * 
     * @param string $id Id of the Category
     *
     * @throws CategoryNotFoundException when Invalid id is provided
     * @return Models\Category Model for the Category
     */
    public function modelFromId($id)
    {
        $category = $this->_collection->findOne(array("id"=> $id));
        if ($category) {
            return new Models\Category($category);
        } else {
            throw new Exceptions\Category\CategoryNotFoundException("Category with ID $id Not Found");
        }
    }

    /**
     * Get a Category Model using a Query
     * 
     * @param array $query Query for the model
     *
     * @throws CategoryNotFoundException if a Category which matches criteria cannot be found
     * @return Models\Category Model of the matching user
     */
    public function modelFromQuery(array $query)
    {
        $category = $this->_collection->findOne($query);

        if ($category) {
            return new Models\Group($category);
        } else {
            throw new Exceptions\Category\CategoryNotFoundException("No Category Matching Query");
        }
            
    }

    /**
     * Find a Category
     * 
     * @param mixed $param Parameter to Find. Pass an Id or a query
     * 
     * @return array document for the category
     */
    public function find($param)
    {
        if (is_array($param)) {
            return $this->modelFromQuery($param)->document();
        } else {
            return $this->modelFromId($param)->document();
        }
    }

    /**
     * Add a Category
     * 
     * @param Models\Category $category Model of the Category
     *
     * @return void
     */
    public function create(Models\Category $category)
    {
        try {
            $temp = $this->modelFromId($category->id);
        } catch (Exceptions\Category\CategoryNotFoundException $e) {
            $category->setId(
                $this->_collections->getCollectionFor("counters")->nextId("category")
            );

            $this->_collection->insert($category->document());
            return $category->id;
        }

        throw new Exceptions\Category\DuplicateCategoryException("Category with this ID already registered");
    }

    /**
     * Remove a Category
     * 
     * @param Integer $categoryId id of the Category
     *
     * @return void
     */
    public function remove($categoryId)
    {
        try
        {
            $this->_collection->remove(array("id"=>$categoryId));
        }
        catch(Exceptions\Category\CategoryNotFoundException $e)
        {
            throw new Exception("Category with this ID not found");
        } 
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
