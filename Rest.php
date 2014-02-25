<?php
namespace SlaxWeb\Rest;

/**
 * Library handling REST calls and REST API server.
 *
 * This file is part of "SlaxWeb Framework".
 *
 * "SlaxWeb Framework" is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Foobar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Foobar.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Tomaz Lovrec <tomaz.lovrec@gmail.com>
 */
class Rest
{
    /**
     * Config array
     *
     * @var array
     */
    protected $_config = array();

    /**
     * Default class constructor
     *
     * Initialize the provided operation mode. Default mode is CLIENT.
     * @param $mode int Mode of the library to initialize
     */
    public function __construct($mode = 0)
    {
        // load the config
        require 'Config/RestConfig.php';
        $this->_config = $config;

        switch ($mode) {
            case 0:
                $this->client = new Client\Client($this->_config);
                break;
            case 1:
                break;
        }
    }
}

/**
 * End of file ./Library/Rest/Rest.php
 */