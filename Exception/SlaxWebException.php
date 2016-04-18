<?php
namespace SlaxWeb\Exception;

class SlaxWebException extends \Exception
{
	/**
	 * Default constructor
	 *
	 * Interpolate the message and context, and send it to the 
	 * PHP Exception class.
	 *
	 * @param $message string Error message
	 * @param $code mixed Error code
	 * @param $context array Context array
	 * @param $previous object Previous exceptions if nested exception
	 */
	public function __construct(
		$message,
		$code = 0,
		array $context = array(),
		Exception $previous = null
	) {
		$message = $this->_interpolate($message, $context);
		parent::__construct($message, $code, $previous);
	}

	/**
     * Interpolate context values into the message placeholders
     *
     * @param $message string Log message
     * @param $context array Context values that replace placeholders in message
     * @return string Interpolated string
     */
    protected function _interpolate($message, array $context = array ())
    {
        // build a replacement array with braces around the context keys
        $replace = array ();
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }
}
