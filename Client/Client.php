<?php
namespace SlaxWeb\Rest\Client;

use SlaxWeb\Exception\SlaxWebException;

/**
 * Rest library helper handling client calls
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
class Client
{
    /**
     * REST payload to be sent in JSON
     *
     * @var string
     */
    protected $_payload = '';
    /**
     * REST url
     *
     * @var string
     */
    protected $_url = '';
    /**
     * Socket timeout
     *
     * Wait this many seconds when checking for url availability
     *
     * @var int
     */
    protected $_socketTimeout = 0;
    /**
     * Config
     *
     * @var array
     */
    protected $_config = array();

    /**
     * Default class constructor
     */
    public function __construct($config)
    {
        // set config
        $this->_config = $config;

        // set the default socket timeout for availability checks
        $this->setSocketTimeout = $this->_config['availability_default_timeout'];
    }

    /**
     * Set the payload
     *
     * @param $payload array Array
     */
    public function setPayload(array $payload)
    {
        // convert the array to JSON string and save it to the payload
        $this->_payload = json_encode($payload);
    }

    /**
     * Sets the URL for the REST call
     *
     * @param $url string URL of the REST to call
     * @return bool If the URL provided is in correct form, true is returned, flase otherwise
     */
    public function setUrl($url)
    {
        // check if we have a valid URL
        if (filter_var($url, FILTER_VALIDATE_URL) !== true) {
            $this->_url = $url;
            // everything is ok
            return true;
        } else {
            // provided string is not an url
            return false;
        }
    }

    /**
     * Set the socket timeout for availability checks
     *
     * @param $seconds int Timeout in seconds
     * @return bool Status
     */
    public function setSocketTimeout($seconds)
    {
        if (is_int($seconds) === true) {
            $this->_socketTimeout = $seconds;
            return true;
        } else {
            // not int
            return false;
        }
    }

    /**
     * Check availability of the set url
     *
     * @return bool Status
     */
    public function checkAvailability()
    {
        // stop error reporting, and set default socket timeout, in case url is not reachable
        // get current error_reporting
        $errorReporting = error_reporting();
        error_reporting(0);
        $oldSocketTimeout = ini_get('default_socket_timeout');
        ini_set("default_socket_timeout", $this->_socketTimeout);
        $status = true;
        try {
            $url = $this->_url;
            $urlData = parse_url($url);
            if ($urlData !== false) {
                $errNumber = '';
                $errString = '';
                $socketHandle = 0;

                // try and open the socket
                $socketHandle = fsockopen($urlData['host'], $urlData['port'], $errNumber, $errString);
                if ($socket !== 0) {
                    $path = '';
                    // set the path and query string
                    if (isset($urlData['path']) === true) {
                        $path .= $urlData['path'];
                    }
                    if (isset($urlData['query']) === true) {
                        $path .= '?' . $urlData['query'];
                    }

                    // set the request
                    $request = "GET {$path} HTTP/1.1\r\n";
                    $request .= "Host: {$urlData['host']}:{$urlData['port']}\r\n";
                    $request .= "Connection: keep-alive\r\n\r\n";

                    // send the request to the url
                    fwrite($socketHandle, $request);
                    // get the response, status code, and close the socket
                    $content = fgets($socketHandle);
                    $statusCode = trim(substr($content, 9, 4));
                    fclose($socketHandle);
                } else {
                    // couldn't open socket
                    return false;
                }
            } else {
                // url is not in correct form
                return false;
            }
        } catch (Exception $exception) {
            // something went wrong
            $status = false;
        }
        // re-set old error_reporting
        error_reporting($errorReporting);
        ini_set("default_socket_timeout", $oldSocketTimeout);
        return $status;
    }

    /**
     * Process the REST call
     *
     * @return mixed Returns the array recieved from the REST call or false on failure
     */
    public function process() {
        if ($this->_url !== '' && $this->_payload !== '') {
            // set headers
            $headers = array (
                'Accept: application/json',
                'Content-Type: application/json'
            );
            // call result and return code
            $result = '';
            $code = '';

            // setup curl for the call
            $curlHandle = curl_init();
            curl_setopt($curlHandle, CURLOPT_URL, $this->_url);
            curl_setopt($curlHandle, CURLOPT_HTTPHEADER,$headers);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST,false);
			curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER,false);
			curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 2);
			curl_setopt($curlHandle, CURLOPT_TIMEOUT, 2);
            curl_setopt($curlHandle, CURLOPT_POST,true);
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS,$this->_payload);
            // everything is set, try and exec the request
            try {
                // get the result and response http code
                $result = curl_exec($curlHandle);
                $code = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
                // close curl
                curl_close($curlHandle);

                // check the response code
                $status = (intval($code / 100) === 2) ? true : false;
                if($status !== false) {
                    return $result;
                } else {
					// wrong status
					$msg = "REST call return status was {returnCode}\n{response}\n";
					$context = array("returnCode" => $code, "response" => $result);
					throw new SlaxWebException($msg, 10001, $context);
                }
            } catch (Exception $exception) {
				// something went wrong
				$msg = "REST call failed, response:\n({response})\nStatus code: <{returnCode}>";
				$context = array("returnCode" => $code, "response" => $result);
				throw new SlaxWebException($msg, 10002, $context);
            }
        } else {
			// url or payload are not set
			$msg = "No data set to process";
			throw new SlaxWebException($msg, 10003);
        }
    }
}

/**
 * End of file ./Rest/Client/Client.php
 */