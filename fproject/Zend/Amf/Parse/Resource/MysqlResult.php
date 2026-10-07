<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Amf
 * @subpackage Parse
 * @copyright  Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Legacy adapter for the removed ext/mysql extension.
 *
 * @package    Zend_Amf
 * @subpackage Parse
 * @copyright  Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Amf_Parse_Resource_MysqlResult
{
    /**
     * @var array List of Mysql types with PHP counterparts
     *
     * Key => Value is Mysql type (exact string) => PHP type
     */
    static public $fieldTypes = array(
        "int" => "int",
        "timestamp" => "int",
        "year" => "int",
        "real" => "float",
    );
    /**
     * Parse resource into array
     *
     * @param resource $resource
     * @return array
     */
    public function parse($resource) {
        throw new \BadMethodCallException(
            'The ext/mysql extension is not available on PHP 8.4. '
            . 'Use Zend_Amf_Parse_Resource_MysqliResult with a mysqli result instead.'
        );
    }
}
