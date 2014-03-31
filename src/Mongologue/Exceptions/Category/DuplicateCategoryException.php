<?php
/**
 * File Containing Exception for Category duplicate
 *
 * @category Mongologue
 * @package  Exceptions
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @author   @naveenbos <nmohanan@suyati.com>
 * @license  NONE http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
namespace Mongologue\Exceptions\Category;

/**
 * Exception for Category duplicate
 *
 * @category Mongologue
 * @package  Exceptions
 * @author   @kpnunni <krishnanunni@suyati.com>
 * @author   @naveenbos <nmohanan@suyati.com>
 * @license  NONE http://github.com/suyati/mongologue
 * @link     http://github.com/suyati/mongologue
 */
class DuplicateCategoryException extends \Exception
{
	/**
     * Constructor for custom Exception
     * 
     * @param string    $message  Message for the Exception
     * @param integer   $code     Exception Code
     * @param Exception $previous Prevoious exception if any
     */
    public function __construct($message, $code=312, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
?>