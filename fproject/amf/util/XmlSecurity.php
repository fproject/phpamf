<?php
///////////////////////////////////////////////////////////////////////////////
//
// © Copyright f-project.net 2010-present.
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//     http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.
//
///////////////////////////////////////////////////////////////////////////////

namespace fproject\amf\util;

use fproject\amf\AmfException;
use DOMDocument;
use SimpleXMLElement;

/**
 * XML Security helper
 */
class XmlSecurity
{
    const ENTITY_DETECT = 'Detected use of ENTITY in XML, disabled to prevent XXE/XEE attacks';

    /**
     * Heuristic scan to detect entity in XML
     *
     * @param  string $xml
     * @throws AmfException
     */
    protected static function heuristicScan($xml)
    {
        if (strpos($xml, '<!ENTITY') !== false) {
            throw new AmfException(self::ENTITY_DETECT);
        }
    }

    /**
     * @param integer $errno
     * @param string $errstr
     * @param string $errfile
     * @param integer $errline
     * @return bool
     */
    public static function loadXmlErrorHandler($errno, $errstr, $errfile, $errline)
    {
        if (substr_count($errstr, 'DOMDocument::loadXML()') > 0) {
            return true;
        }
        return false;
    }

    /**
     * Scan XML string for potential XXE and XEE attacks
     *
     * @param   string $xml
     * @param   SimpleXMLElement|DomDocument $dom
     * @throws  AmfException
     * @return  SimpleXMLElement|DomDocument|boolean
     */
    public static function scan($xml, DOMDocument $dom = null)
    {
        // If running with PHP-FPM we perform an heuristic scan
        // We cannot use libxml_disable_entity_loader because of this bug
        // @see https://bugs.php.net/bug.php?id=64938
        if (self::isPhpFpm()) {
            self::heuristicScan($xml);
        }

        if (null === $dom) {
            $simpleXml = true;
            $dom = new DOMDocument();
        }

        if (!self::isPhpFpm()) {
            $loadEntities = libxml_disable_entity_loader(true);
            $useInternalXmlErrors = libxml_use_internal_errors(true);
        }

        // Load XML with network access disabled (LIBXML_NONET)
        // error disabled with @ for PHP-FPM scenario
        set_error_handler(array('XmlSecurity', 'loadXmlErrorHandler'), E_WARNING);

        $result = $dom->loadXML($xml, LIBXML_NONET);
        restore_error_handler();

        if (!$result) {
            // Entity load to previous setting
            if (!self::isPhpFpm()) {
                libxml_disable_entity_loader($loadEntities);
                libxml_use_internal_errors($useInternalXmlErrors);
            }
            return false;
        }

        // Scan for potential XEE attacks using ENTITY, if not PHP-FPM
        if (!self::isPhpFpm()) {
            foreach ($dom->childNodes as $child) {
                if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
                    if ($child->entities->length > 0) {
                        throw new AmfException(self::ENTITY_DETECT);
                    }
                }
            }
        }

        // Entity load to previous setting
        if (!self::isPhpFpm()) {
            libxml_disable_entity_loader($loadEntities);
            libxml_use_internal_errors($useInternalXmlErrors);
        }

        if (isset($simpleXml)) {
            $result = simplexml_import_dom($dom);
            if (!$result instanceof SimpleXMLElement) {
                return false;
            }
            return $result;
        }
        return $dom;
    }

    /**
     * Scan XML file for potential XXE/XEE attacks
     *
     * @param  string $file
     * @param  DOMDocument $dom
     * @throws AmfException
     * @return SimpleXMLElement|DomDocument
     */
    public static function scanFile($file, DOMDocument $dom = null)
    {
        if (!file_exists($file)) {
            throw new AmfException(
                "The file $file specified doesn't exist"
            );
        }
        return self::scan(file_get_contents($file), $dom);
    }

    /**
     * Return true if PHP is running with PHP-FPM
     *
     * @return boolean
     */
    public static function isPhpFpm()
    {
        if (substr(php_sapi_name(), 0, 3) === 'fpm') {
            return true;
        }
        return false;
    }
}
