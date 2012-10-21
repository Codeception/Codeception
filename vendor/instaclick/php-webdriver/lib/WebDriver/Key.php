<?php
/**
 * Copyright 2011-2012 Anthon Pang. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package WebDriver
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 * @author Fabrizio Branca <mail@fabrizio-branca.de>
 */

namespace WebDriver;

/**
 * WebDriver\Key class
 *
 * @package WebDriver
 */
final class Key
{
    /*
     * The Unicode "Private Use Area" code points (0xE000-0xF8FF) are used to represent
     * pressable, non-text keys.
     *
     * @link http://code.google.com/p/selenium/wiki/JsonWireProtocol#/session/:sessionId/element/:id/value
     *
     *    key_name    = "UTF-8";        // UCS-2
     */
    const NULL_KEY    = "\xEE\x80\x80"; // E000
    const CANCEL      = "\xEE\x80\x81"; // E001
    const HELP        = "\xEE\x80\x82"; // E002
    const BACKSPACE   = "\xEE\x80\x83"; // E003
    const TAB         = "\xEE\x80\x84"; // E004
    const CLEAR       = "\xEE\x80\x85"; // E005
    const RETURN_KEY  = "\xEE\x80\x86"; // E006
    const ENTER       = "\xEE\x80\x87"; // E007
    const SHIFT       = "\xEE\x80\x88"; // E008
    const CONTROL     = "\xEE\x80\x89"; // E009
    const ALT         = "\xEE\x80\x8A"; // E00A
    const PAUSE       = "\xEE\x80\x8B"; // E00B
    const ESCAPE      = "\xEE\x80\x8C"; // E00C
    const SPACE       = "\xEE\x80\x8D"; // E00D
    const PAGE_UP     = "\xEE\x80\x8E"; // E00E
    const PAGE_DOWN   = "\xEE\x80\x8F"; // E00F
    const END         = "\xEE\x80\x90"; // E010
    const HOME        = "\xEE\x80\x91"; // E011
    const LEFT_ARROW  = "\xEE\x80\x92"; // E012
    const UP_ARROW    = "\xEE\x80\x93"; // E013
    const RIGHT_ARROW = "\xEE\x80\x94"; // E014
    const DOWN_ARROW  = "\xEE\x80\x95"; // E015
    const INSERT      = "\xEE\x80\x96"; // E016
    const DELETE      = "\xEE\x80\x97"; // E017
    const SEMICOLON   = "\xEE\x80\x98"; // E018
    const EQUALS      = "\xEE\x80\x99"; // E019
    const NUMPAD_0    = "\xEE\x80\x9A"; // E01A
    const NUMPAD_1    = "\xEE\x80\x9B"; // E01B
    const NUMPAD_2    = "\xEE\x80\x9C"; // E01C
    const NUMPAD_3    = "\xEE\x80\x9D"; // E01D
    const NUMPAD_4    = "\xEE\x80\x9E"; // E01E
    const NUMPAD_5    = "\xEE\x80\x9F"; // E01F
    const NUMPAD_6    = "\xEE\x80\xA0"; // E020
    const NUMPAD_7    = "\xEE\x80\xA1"; // E021
    const NUMPAD_8    = "\xEE\x80\xA2"; // E022
    const NUMPAD_9    = "\xEE\x80\xA3"; // E023
    const MULTIPLY    = "\xEE\x80\xA4"; // E024
    const ADD         = "\xEE\x80\xA5"; // E025
    const SEPARATOR   = "\xEE\x80\xA6"; // E026
    const SUBTRACT    = "\xEE\x80\xA7"; // E027
    const DECIMAL     = "\xEE\x80\xA8"; // E028
    const DIVIDE      = "\xEE\x80\xA9"; // E029
    const F1          = "\xEE\x80\xB1"; // E031
    const F2          = "\xEE\x80\xB2"; // E032
    const F3          = "\xEE\x80\xB3"; // E033
    const F4          = "\xEE\x80\xB4"; // E034
    const F5          = "\xEE\x80\xB5"; // E035
    const F6          = "\xEE\x80\xB6"; // E036
    const F7          = "\xEE\x80\xB7"; // E037
    const F8          = "\xEE\x80\xB8"; // E038
    const F9          = "\xEE\x80\xB9"; // E039
    const F10         = "\xEE\x80\xBA"; // E03A
    const F11         = "\xEE\x80\xBB"; // E03B
    const F12         = "\xEE\x80\xBC"; // E03C
    const COMMAND     = "\xEE\x80\xBD"; // E03D
    const META        = "\xEE\x80\xBD"; // E03D
}
