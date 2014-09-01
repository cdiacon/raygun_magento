<?php
class Cdl_Raygun_Helper_Data extends Mage_Core_Helper_Abstract
{
	const XML_RAYGUN_APPLICATION_CODE    = 'dev/log/raygun_code';


	/**
	 * Check for raygun code enabled.
	 *
	 * @return bool
	 */
	public function raygunEnabled() {
		return (bool)Mage::getStoreConfig(self::XML_RAYGUN_APPLICATION_CODE);
	}


	/**
	 * Send message to raygun.
	 *
	 * @param $message
	 */
	public function log( $message )
	{

	}

	/**
	 * Raygun exception.
	 *
	 * @param $errno
	 * @param $message
	 * @param $file
	 * @param $line
	 * @param $tags
	 * @param $time
	 */
	public function logException( $errno, $message, $file, $line, $tags, $time )
	{

	}

	/**
	 * Initiate a raygun client.
	 *
	 * @param bool $async
	 * @param bool $debug
	 * @param bool $userTrack
	 *
	 * @return bool|\Raygun4php\RaygunClient
	 */
	public function getRaygunClient($async = true, $debug = false, $userTrack = false)
	{
		$code = Mage::getstoreConfig(self::XML_RAYGUN_APPLICATION_CODE);

		if ($this->raygunEnabled()) {
			require_once Mage::getBaseDir('lib').DS.'Raygun4php/RaygunClient.php';
			return new Raygun4php\RaygunClient($code, $async, $debug, $userTrack);
		}

		return false;
	}

	/**
	 * Custom error handler.
	 * @param $errno
	 * @param $errstr
	 * @param $errfile
	 * @param $errline
	 *
	 * @throws Exception
	 */
	function customRaygunErrorHandler($errno, $errstr, $errfile, $errline){

		if (strpos($errstr, 'DateTimeZone::__construct')!==false) {
			// there's no way to distinguish between caught system exceptions and warnings
			return false;
		}

		$errno = $errno & error_reporting();

		if ($errno == 0) {
			return false;
		}
		if (!defined('E_STRICT')) {
			define('E_STRICT', 2048);
		}
		if (!defined('E_RECOVERABLE_ERROR')) {
			define('E_RECOVERABLE_ERROR', 4096);
		}
		if (!defined('E_DEPRECATED')) {
			define('E_DEPRECATED', 8192);
		}

		// PEAR specific message handling
		if (stripos($errfile.$errstr, 'pear') !== false) {
			// ignore strict and deprecated notices
			if (($errno == E_STRICT) || ($errno == E_DEPRECATED)) {
				return true;
			}
			// ignore attempts to read system files when open_basedir is set
			if ($errno == E_WARNING && stripos($errstr, 'open_basedir') !== false) {
				return true;
			}
		}

		$errorMessage = '';

		switch($errno){
			case E_ERROR:
				$errorMessage .= "Error";
				break;
			case E_WARNING:
				$errorMessage .= "Warning";
				break;
			case E_PARSE:
				$errorMessage .= "Parse Error";
				break;
			case E_NOTICE:
				$errorMessage .= "Notice";
				break;
			case E_CORE_ERROR:
				$errorMessage .= "Core Error";
				break;
			case E_CORE_WARNING:
				$errorMessage .= "Core Warning";
				break;
			case E_COMPILE_ERROR:
				$errorMessage .= "Compile Error";
				break;
			case E_COMPILE_WARNING:
				$errorMessage .= "Compile Warning";
				break;
			case E_USER_ERROR:
				$errorMessage .= "User Error";
				break;
			case E_USER_WARNING:
				$errorMessage .= "User Warning";
				break;
			case E_USER_NOTICE:
				$errorMessage .= "User Notice";
				break;
			case E_STRICT:
				$errorMessage .= "Strict Notice";
				break;
			case E_RECOVERABLE_ERROR:
				$errorMessage .= "Recoverable Error";
				break;
			case E_DEPRECATED:
				$errorMessage .= "Deprecated functionality";
				break;
			default:
				$errorMessage .= "Unknown error ($errno)";
				break;
		}

		$errorMessage .= ": {$errstr}  in {$errfile} on line {$errline}";

		if (Mage::getIsDeveloperMode()) {
			throw new Exception($errorMessage);
		} else {
			//raygun send message.
			if ($this->raygunEnabled()) {

				$rayClient = $this->getRaygunClient(true, true, false);
				$rayClient->SendError($errno, $errstr, $errfile, $errline);
			}

			Mage::log($errorMessage, Zend_Log::ERR);
		}
	}

}