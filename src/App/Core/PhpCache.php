<?php

/***************************************************************/
/* PhpCache - a class for caching arbitrary data

  Software License Agreement (BSD License)

  Copyright (C) 2005-2007, Edward Eliot.
  All rights reserved.

  Redistribution and use in source and binary forms, with or without
  modification, are permitted provided that the following conditions are met:

	 * Redistributions of source code must retain the above copyright
	   notice, this list of conditions and the following disclaimer.
	 * Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.
	 * Neither the name of Edward Eliot nor the names of its contributors
	   may be used to endorse or promote products derived from this software
	   without specific prior written permission of Edward Eliot.

  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS "AS IS" AND ANY
  EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
  WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
  DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY
  DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
  (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
  ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

  Last Updated:  7th January 2007                             */
/***************************************************************/

namespace App\Core;

class PhpCache
{
    private $file;
    private $fileLock;
    private $cacheTime;

    private $cachePath = './app/cache';

    /**
     * @param $key
     * @param $cacheTime
     *
     */
    public function __construct($key, $cacheTime)
    {
        $this->file = $this->cachePath . '/' . md5($key) . '.txt';
        $this->fileLock = $this->file . '.lock';
        $this->cacheTime = $cacheTime >= 10 ? $cacheTime : 10;
    }

    /**
     * @return bool
     */
    public function check()
    {
        if (file_exists($this->fileLock)) {
            return true;
        }
        return (file_exists($this->file) && ($this->cacheTime == -1 || time() - filemtime($this->file) <= $this->cacheTime));
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return (file_exists($this->file) || file_exists($this->fileLock));
    }

    /**
     * @param $content
     *
     * @return bool
     *
     */
    public function set($content)
    {
        if (!file_exists($this->fileLock)) {
            if (file_exists($this->file)) {
                copy($this->file, $this->fileLock);
            }
            $file = fopen($this->file, 'w');
            fwrite($file, serialize($content));
            fclose($file);
            if (file_exists($this->fileLock)) {
                unlink($this->fileLock);
            }
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function get()
    {
        if (file_exists($this->fileLock)) {
            return unserialize(file_get_contents($this->fileLock));
        } else {
            return unserialize(file_get_contents($this->file));
        }
    }

    /**
     *
     */
    public function reValidate()
    {
        touch($this->file);
    }
}
