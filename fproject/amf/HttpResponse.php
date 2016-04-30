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

namespace fproject\amf;

/**
 * Creates the proper http headers and send the serialized AMF stream to standard out.
 *
 */
class HttpResponse extends Response
{
    /**
     * Create the application response header for AMF and sends the serialized AMF string
     *
     * @return string
     */
    public function getResponse()
    {
        if (!headers_sent()) {
            if ($this->isIeOverSsl()) {
                header('Cache-Control: cache, must-revalidate');
                header('Pragma: public');
            } else {
                header('Cache-Control: no-cache, must-revalidate');
                header('Pragma: no-cache');
            }
            header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
            header('Content-Type: application/x-amf');
        }
        return parent::getResponse();
    }

    protected function isIeOverSsl()
    {
        $ssl = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : false;
        if (!$ssl || ($ssl == 'off')) {
            // IIS reports "off", whereas other browsers simply don't populate
            return false;
        }

        $ua  = $_SERVER['HTTP_USER_AGENT'];
        if (!preg_match('/; MSIE \d+\.\d+;/', $ua)) {
            // Not MicroSoft Internet Explorer
            return false;
        }

        return true;
    }
}
