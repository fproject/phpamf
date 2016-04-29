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
 * This is the type of message returned by the MessageBroker
 * to endpoints after the broker has routed an endpoint's message
 * to a service.
 *
 * flex.messaging.messages.AcknowledgeMessage
 *
 */
class AcknowledgeMessage extends AsyncMessage
{
    /**
     * Create a new Acknowledge Message
     *
     * @param mixed $message
     */
    public function __construct($message)
    {
        $this->clientId    = $this->generateId();
        $this->destination = null;
        $this->messageId   = $this->generateId();
        $this->timestamp   = time().'00';
        $this->timeToLive  = 0;
        $this->headers     = new \stdClass();
        $this->body        = null;

        // correlate the two messages
        if ($message && isset($message->messageId)) {
            $this->correlationId = $message->messageId;
        }
    }
}
