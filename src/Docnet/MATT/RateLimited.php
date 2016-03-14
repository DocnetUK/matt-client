<?php
/**
 * Copyright 2016 Venditan Limited
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Docnet\MATT;

use Docnet\MATT;

/**
 * Rate limited MATT Client
 *
 * Allows for limiting the number of identical messages generated during a single execution thread
 *
 * @author Craig McMahon <cmcmahon@docnet.nu>
 */
class RateLimited extends MATT
{

    /**
     * Array of notifications we are keeping track of
     * @var array
     */
    protected static $notifications = array();

    /**
     * Period in seconds between identical notifications being sent
     * @var int
     */
    protected static $rateLimit = 60;

    /**
     * Send the notification if we should
     * @return bool
     */
    public function send()
    {
        if ($this->shouldSend()) {
            if (parent::send()) {
                $this->markSent();
                return true;
            }
            return false;
        } else {
            // This prevents send being called twice
            $this->bol_sent = true;
            return true;
        }
    }

    /**
     * Determine if we should send the current notification
     * @return bool
     */
    protected function shouldSend()
    {
        if (!isset(static::$notifications[$this->str_event])
            || !isset(static::$notifications[$this->str_event][$this->str_app])
            || (time() >= static::$notifications[$this->str_event][$this->str_app] + static::$rateLimit)
        ) {
            return true;
        }
        return false;
    }

    /**
     * Record current notification as sent
     */
    protected function markSent()
    {
        if (!isset(static::$notifications[$this->str_event])) {
            static::$notifications[$this->str_event] = array(
                $this->str_app => time()
            );
            return;
        }

        static::$notifications[$this->str_event][$this->str_app] = time();
    }

}