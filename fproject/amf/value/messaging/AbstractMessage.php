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

namespace fproject\amf\value\messaging;

/**
 * This is the default Implementation of Message, which provides
 * a convenient base for behavior and association of common endpoints
 *
 */
class AbstractMessage
{
    /**
     * @var string Client identifier
     */
    public $clientId;

    /**
     * @var string Destination
     */
    public $destination;

    /**
     * @var string Message identifier
     */
    public $messageId;

    /**
     * @var int Message timestamp
     */
    public $timestamp;

    /**
     * @var int Message TTL
     */
    public $timeToLive;

    /**
     * @var object Message headers
     */
    public $headers;

    /**
     * @var string|array Message body
     */
    public $body;

    /**
     * Generate a unique id
     *
     * Format is: ########-####-####-####-############
     * Where # is an uppercase letter or number
     * example: 6D9DC7EC-A273-83A9-ABE3-00005FD752D6
     *
     * @return string
     */
    public function generateId()
    {
        return sprintf(
            '%08X-%04X-%04X-%02X%02X-%012X',
            mt_rand(),
            mt_rand(0, 65535),
            bindec(substr_replace(
                sprintf('%016b', mt_rand(0, 65535)), '0100', 11, 4)
            ),
            bindec(substr_replace(sprintf('%08b', mt_rand(0, 255)), '01', 5, 2)),
            mt_rand(0, 255),
            mt_rand()
        );
    }
}
