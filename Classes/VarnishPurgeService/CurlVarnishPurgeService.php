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
 * when a PURGE request is received.
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
	 * Constructor
	 */
	public function __construct() {
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moc_varnish']);
	}

	/**
	 * If set, the actual CURL call are executed when the clearCacheForUrl is called, otherwise the execute
	 * methods is called when the PHP Garbage cleaning is running.
	 *
	 * @var boolean
	 */
	public static $executeImmediately = FALSE;

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
		if (!in_array($url, array('.*', '/'), TRUE) && ((boolean)$this->extConf['appendWildcard'] === TRUE)) {
			$path .= '.*';
		}
		array_push($this->clearQueue, $path);
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
		$curlHandles = array();
		if (count($this->clearQueue) > 0) {
			$this->clearQueue = array_unique($this->clearQueue);
			GeneralUtility::devLog('Curl HTTP Purge service clearing cache', 'moc_varnish', 0, $this->clearQueue);
			$mh = curl_multi_init();
			$varnishHosts = array();
			if (isset($this->extConf['varnishHosts']) && $this->extConf['varnishHosts'] !== '') {
				$varnishHosts = GeneralUtility::trimExplode(',', $this->extConf['varnishHosts'], TRUE);
			}
			foreach ($this->clearQueue as $path) {
				if (count($varnishHosts) > 0) {
					foreach ($varnishHosts as $varnishHost) {
						$ch = $this->getCurlHandleForCacheClearing($path, $varnishHost);
						array_push($curlHandles, $ch);
						curl_multi_add_handle($mh, $ch);
					}
				} else {
					$ch = $this->getCurlHandleForCacheClearing($path);
					array_push($curlHandles, $ch);
					curl_multi_add_handle($mh, $ch);
				}
			}

			$active = NULL;
			do {
				$mrc = curl_multi_exec($mh, $active);
			} while ($mrc == CURLM_CALL_MULTI_PERFORM);

			while ($active && $mrc == CURLM_OK) {
				if (curl_multi_select($mh) !== -1) {
					do {
						$mrc = curl_multi_exec($mh, $active);
					} while ($mrc == CURLM_CALL_MULTI_PERFORM);
				}
			}

			foreach ($curlHandles as $ch) {
				curl_close($ch);
				curl_multi_remove_handle($mh, $ch);
			}

			curl_multi_close($mh);
			$this->clearQueue = array();
		}
	}

	/**
	 * @param string $url The URL (absolute) to clear for.
	 * @param string $varnishHost Optional override which IP (and port) the varnish server is running on.
	 * @return resource
	 */
	protected function getCurlHandleForCacheClearing($url, $varnishHost = NULL) {
		$curlHandle = curl_init();
		if ($varnishHost !== NULL) {
			$parsedUrl = parse_url($url);
			$domainToClearFor = $parsedUrl['host'];
			curl_setopt($curlHandle, CURLOPT_URL, str_replace($domainToClearFor, $varnishHost, $url));
			curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array('Host: ' . $domainToClearFor));
		} else {
			curl_setopt($curlHandle, CURLOPT_URL, $url);
		}
		curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'PURGE');
		curl_setopt($curlHandle, CURLOPT_HEADER, FALSE);
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, TRUE);
		return $curlHandle;
	}

	/**
	 * When this destructor is called by PHP Garbage collection, all stored URL's to purge will
	 * be executed, ie. sent as HTTP Purge requests to varnish.
	 */
	public function __destruct() {
		$this->execute();
	}

}