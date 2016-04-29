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
 * This type of message contains information needed to perform
 * a Remoting invocation.
 *
 * Corresponds to flex.messaging.messages.RemotingMessage
 *
 */
class RemotingMessage extends AbstractMessage
{

    /**
     * The name of the service to be called including package name
     * @var String
     */
    public $source;

    /**
     * The name of the method to be called
     * @var string
     */
    public $operation;

    /**
     * The arguments to call the mathod with
     * @var array
     */
    public $parameters;

    /**
     * Create a new Remoting Message
     *
     */
    public function __construct()
    {
        $this->clientId    = $this->generateId();
        $this->destination = null;
        $this->messageId   = $this->generateId();
        $this->timestamp   = time().'00';
        $this->timeToLive  = 0;
        $this->headers     = new \stdClass();
        $this->body        = null;
    }
}
