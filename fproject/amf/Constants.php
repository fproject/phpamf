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
 * The following constants are used throughout serialization and
 * deserialization to detect the AMF marker and encoding types.
 *
 */
final class Constants
{
    const AMF0_NUMBER            = 0x00;
    const AMF0_BOOLEAN           = 0x01;
    const AMF0_STRING            = 0x02;
    const AMF0_OBJECT            = 0x03;
    const AMF0_MOVIECLIP         = 0x04;
    const AMF0_NULL              = 0x05;
    const AMF0_UNDEFINED         = 0x06;
    const AMF0_REFERENCE         = 0x07;
    const AMF0_MIXEDARRAY        = 0x08;
    const AMF0_OBJECTTERM        = 0x09;
    const AMF0_ARRAY             = 0x0a;
    const AMF0_DATE              = 0x0b;
    const AMF0_LONGSTRING        = 0x0c;
    const AMF0_UNSUPPORTED       = 0x0e;
    const AMF0_XML               = 0x0f;
    const AMF0_TYPEDOBJECT       = 0x10;
    const AMF0_AMF3              = 0x11;
    const AMF0_OBJECT_ENCODING   = 0x00;

    const AMF3_UNDEFINED         = 0x00;
    const AMF3_NULL              = 0x01;
    const AMF3_BOOLEAN_FALSE     = 0x02;
    const AMF3_BOOLEAN_TRUE      = 0x03;
    const AMF3_INTEGER           = 0x04;
    const AMF3_NUMBER            = 0x05;
    const AMF3_STRING            = 0x06;
    const AMF3_XML               = 0x07;
    const AMF3_DATE              = 0x08;
    const AMF3_ARRAY             = 0x09;
    const AMF3_OBJECT            = 0x0A;
    const AMF3_XMLSTRING         = 0x0B;
    const AMF3_BYTEARRAY         = 0x0C;
    const AMF3_VECTOR_INT        = 0x0D;
    const AMF3_VECTOR_UINT       = 0x0E;
    const AMF3_VECTOR_NUMBER     = 0x0F;
    const AMF3_VECTOR_OBJECT     = 0x10;
    const AMF3_DICTIONARY        = 0x11;

    const AMF3_OBJECT_ENCODING   = 0x03;

    // Object encodings for AMF3 object types
    const ET_PROPLIST            = 0x00;
    const ET_EXTERNAL            = 0x01;
    const ET_DYNAMIC             = 0x02;
    const ET_PROXY               = 0x03;

    const FMS_OBJECT_ENCODING    = 0x01;

    /**
     * Special content length value that indicates "unknown" content length
     * per AMF Specification
     */
    const UNKNOWN_CONTENT_LENGTH = -1;
    const URL_APPEND_HEADER      = 'AppendToGatewayUrl';
    const RESULT_METHOD          = '/onResult';
    const STATUS_METHOD          = '/onStatus';
    const CREDENTIALS_HEADER     = 'Credentials';
    const PERSISTENT_HEADER      = 'RequestPersistentHeader';
    const DESCRIBE_HEADER        = 'DescribeService';

    const GUEST_ROLE             = 'anonymous';
}
