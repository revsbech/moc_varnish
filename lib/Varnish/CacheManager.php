<?php
interface Varnish_CacheMangerInterface {

	/**
	 * Clear cache for a given urls, possibly on a certains domain.
	 * The second paramater to the function, is the domain for which the URL should cleared.
	 * If left empty (the default), it will be cleared for all domain.
	 *
	 * @param string $url The URL to cleare cache for
	 * @param string $domain The domain for which the URL should is to be cleared. If left empty, means all (The defalt is empty).
	 * @return void
	 */
	public function clearCacheForUrl($url, $domain = '');

}

class Varnish_CacheManager_CURLHTTP implements Varnish_CacheMangerInterface {

	/**
	 * @var array
	 */
	protected $clearQueue = array();

	/**
	 * @var array
	 */
	protected $extConf = array();

	/**
	 * Create an instance of the CURLHTTP cachemanager. IT takes one parameter, the HTTP address
	 * (including http://) that the Varnish server is running on. If this parameter is specified
	 * This one is used, otherwise, the host of the URL that needs to cleared is used.
	 */
	public function __construct() {
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moc_varnish']);
	}

	/**
	 * Clear cache for a given urls, possibly on a certains domain.
	 * The second paramater to the function, is the domain for which the URL should cleared.
	 * If left empty (the default), it will be cleared for all domain.
	 *
	 * @param string $url
	 * @param string $domain
	 * @param string $scheme
	 * @return void
	 */
	public function clearCacheForUrl($url, $domain = '', $scheme = 'http://') {
		if ($domain) {
			$parsedDomain = parse_url($domain);
			$path = $scheme . str_replace('/', '', isset($parsedDomain['host']) ? $parsedDomain['host'] : $parsedDomain['path']);
		} else {
			$path = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST');
		}
		if (substr($url, 0, 1) !== '/') {
			$path .= '/';
		}
		$path .= $url;
		if (!in_array($url, array('.*', '/'), TRUE) && ((boolean)$this->extConf['appendWildcard'] === TRUE)) {
			$path .= '.*';
		}
		array_push($this->clearQueue, $path);
	}

	/**
	 * @return void
	 */
	public function execute() {
		$curl_handles = array();
		if (count($this->clearQueue) > 0) {
			$this->clearQueue = array_unique($this->clearQueue);
			t3lib_div::devLog('Clearing cache', 'moc_varnish', 0, $this->clearQueue);
			$mh = curl_multi_init();
			foreach ($this->clearQueue as $path) {
				$ch = $this->getCurlHandleForCacheClearing($path);
				array_push($curl_handles, $ch);
				curl_multi_add_handle($mh, $ch);
			}

			$active = NULL;
			do {
				$mrc = curl_multi_exec($mh, $active);
			} while ($mrc == CURLM_CALL_MULTI_PERFORM);

			while ($active && $mrc == CURLM_OK) {
				if (curl_multi_select($mh) != -1) {
					do {
						$mrc = curl_multi_exec($mh, $active);
					} while ($mrc == CURLM_CALL_MULTI_PERFORM);
				}
			}

			foreach ($curl_handles as $ch) {
				curl_close($ch);
				curl_multi_remove_handle($mh, $ch);
			}

			curl_multi_close($mh);
			$this->clearQueue = array();
		}
	}

	/**
	 * @param string $url
	 * @return resource
	 */
	protected function getCurlHandleForCacheClearing($url) {
		$curlHandle = curl_init();
		curl_setopt($curlHandle, CURLOPT_URL , $url);
		curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'PURGE');
		curl_setopt($curlHandle, CURLOPT_HEADER, 0);
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
		if (intval($this->extConf['overrideVarnishPort'])) {
			curl_setopt($curlHandle, CURLOPT_PORT, intval($this->extConf['overrideVarnishPort']));
		}
		return $curlHandle;
	}

	public function __destruct() {
		$this->execute();
	}

}
?>