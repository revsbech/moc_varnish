<?php
namespace MOC\MocVarnish\VarnishPurgeService;

use MOC\MocVarnish\VarnishPurgeServiceInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Curl HTTP Implementation of the VarnishPurgeServiceInterface for MOC Varnish.
 *
 * This implementation clears cache by sending a HTTP Purge request via CURL
 * to Varnish.
 *
 * It is dependent on varnish being correctly configured to ban cache records
 * when a PURGE request is received. It supports two methods, PURGE a specific URL, or
 * PURGE an object tagged in Varnish with a specific pageId. Internally only the later method
 * is used.
 *
 * The requests are cached internally, and executed when PHP calls the destructor.
 *
 * @package MOC\MocVarnish
 */
class CurlVarnishPurgeService implements VarnishPurgeServiceInterface {

	/**
	 * @var array
	 */
	protected $clearQueue = array();

	/**
	 * @var array
	 */
	protected $extConf = array();

	/**
	 * @var resource
	 */
	protected $curlMultiHandle;

	/**
	 * Array of specific varnish hosts to clear for. If this is set (done in extension manager)
	 * these Varnish hosts are sent the purge requests. Otherwise the domain og of the URL to clear
	 * is used to resolve which varnish host to clear for.
	 *
	 * @var array
	 */
	protected $varnishHosts = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moc_varnish']);
		if (isset($this->extConf['varnishHosts']) && $this->extConf['varnishHosts'] !== '') {
			$this->varnishHosts = GeneralUtility::trimExplode(',', $this->extConf['varnishHosts'], TRUE);
		}
		$this->curlMultiHandle = curl_multi_init();
	}

	/**
	 * If set, the actual CURL call are executed when the clearCacheForUrl is called, otherwise the execute
	 * methods is called when the PHP Garbage cleaning is running.
	 *
	 * @var boolean
	 */
	public static $executeImmediately = FALSE;

	/**
	 * @param string $domain Option domain to clear for.
	 * @return void
	 */
	public function clearAllCache($domain = '', $scheme = 'http://') {
		if (empty($domain)) {
			throw new \RuntimeException('Unable to clear Varnish cache via Curl HTTP Purge service on unknown domain.');
		}
		$parsedDomain = parse_url($domain);
		$url = $scheme . str_replace('/', '', isset($parsedDomain['host']) ? $parsedDomain['host'] : $parsedDomain['path']);
		$this->addCurlHandleToQueue($url, 'BAN', 'Varnish-Ban-All: 1');
	}

	/**
	 * Clear cache for a specific TYPO3 page id by sending a BAN request to Varnish
	 *
	 * @param integer $pageId
	 * @param string $domain
	 * @param string $scheme
	 * @throws \RuntimeException
	 */
	public function clearCacheForTypo3PageId($pageId, $domain, $scheme = 'http://') {
		if (empty($domain)) {
			throw new \RuntimeException('Unable to clear Varnish cache via Curl HTTP Purge service on unknown domain.');
		}
		$parsedDomain = parse_url($domain);
		$url = $scheme . str_replace('/', '', isset($parsedDomain['host']) ? $parsedDomain['host'] : $parsedDomain['path']);
		$this->addCurlHandleToQueue($url, 'BAN', 'Varnish-Ban-TYPO3-Pid: ' . $pageId);
		if (static::$executeImmediately) {
			$this->execute();
		}
	}

	/**
	 * Clear cache for a given urls, possibly on a given domain.
	 *
	 * If domain is left empty, a RuntimeException is thrown.
	 *
	 * @param string $url The URL to clear cache for
	 * @param string $domain A possible domain to clear cache for
	 * @param string $scheme Default http://
	 * @return void
	 * @throws \RuntimeException
	 */
	public function clearCacheForUrl($url, $domain, $scheme = 'http://') {

		if (empty($domain)) {
			throw new \RuntimeException('Unable to clear Varnish cache via Curl HTTP Purge service on unknown domain.');
		}

		$parsedDomain = parse_url($domain);
		$path = $scheme . str_replace('/', '', isset($parsedDomain['host']) ? $parsedDomain['host'] : $parsedDomain['path']);

		if (substr($url, 0, 1) !== '/') {
			$path .= '/';
		}
		$path .= $url;
		$this->addCurlHandleToQueue($path);
		if (static::$executeImmediately) {
			$this->execute();
		}
	}

	/**
	 * Do the actual Curl HTTP Purge requests
	 *
	 * @return void
	 */
	public function execute() {
		GeneralUtility::devLog('Curl HTTP Purge service clearing cache', 'moc_varnish');
		$active = NULL;

		// Handle differently depending on version of libCURL. Only one of the methods below will actually be called.
		do {
			$mrc = curl_multi_exec($this->curlMultiHandle, $active);
		} while ($mrc == CURLM_CALL_MULTI_PERFORM);

		while ($active && $mrc == CURLM_OK) {
			if (curl_multi_select($this->curlMultiHandle) !== -1) {
				do {
					$mrc = curl_multi_exec($this->curlMultiHandle, $active);
				} while ($mrc == CURLM_CALL_MULTI_PERFORM);
			}
		}
		curl_multi_close($this->curlMultiHandle);

			// Re-init the curlHandle
		$this->curlMultiHandle = curl_multi_init();
	}

	/**
	 * Create a CURL handle for clearing cache in varnish. Varnish is expected to be configured to handle both
	 * a PURGE and a BAN request. PURGE is the backward compatible version which ban/purge the specific URL which is
	 * called, whereas a BAN is expected to ban pages with a given TYPO3 page id. The page id to ban is given as
	 * the HTTP header 'Varnish-Ban-TYPO3-Pid: NNN' og 'Varnish-Ban-All: 1' headers.
	 *
	 * @param string $url The URL (absolute) to clear for.
	 * @param string $method The method to use. Depending on varnish setup, might be PURGE og BAN
	 * @param string $varnishHost Optional override which IP (and port) the varnish server is running on.
	 * @param array|string $headers	Custom headers to send to varnish. either a string, or an array of strings
	 * @return resource
	 */

	protected function addCurlHandleToQueue($url, $method = 'PURGE', $headers = '') {

		if(!is_array($headers) && $headers !== '') {
			$headers = array($headers);
		}
		$parsedUrl = parse_url($url);
		$domainToClearFor = $parsedUrl['host'];
		$headers[] = 'Host: ' . $domainToClearFor;

			// If no specific hosts are given, use the domain
		$varnishHosts = count($this->varnishHosts) === 0 ? array($domainToClearFor) : $this->varnishHosts;
		GeneralUtility::devLog('Adding CURL handle', 'moc_varnish', 0, array('url' => $url, 'varnishHosts' => $varnishHosts, 'method' => $method, 'headers' => $headers));
		foreach ($varnishHosts as $varnishHost) {
			$curlHandle = curl_init();
			curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($curlHandle, CURLOPT_HEADER, FALSE);
			curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curlHandle, CURLOPT_URL, str_replace($domainToClearFor, $varnishHost, $url));
			curl_multi_add_handle($this->curlMultiHandle, $curlHandle);
		}
	}

	/**
	 * When this destructor is called by PHP Garbage collection, all stored URL's to purge will
	 * be executed, ie. sent as HTTP Purge requests to varnish.
	 */
	public function __destruct() {
		$this->execute();
	}

}