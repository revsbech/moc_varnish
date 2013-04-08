<?php
require_once(t3lib_extMgm::extPath('moc_varnish') . 'lib/URL/Finder.php');
require_once(t3lib_extMgm::extPath('moc_varnish') . 'lib/Varnish/CacheManager.php');

class tx_mocvarnish_tcemain_cachehooks {

	/**
	 * @var array
	 */
	protected $extConf = array();

	/**
	 * @var Varnish_CacheManager_CURLHTTP
	 */
	protected $varnishCacheMgm;

	public function __construct() {
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moc_varnish']);
		$this->varnishCacheMgm = t3lib_div::makeInstance('Varnish_CacheManager_CURLHTTP');
	}

	/**
	 * Purge the varnish cache when clearing all caches or clearing page content cache.
	 * Finds all rootpages of the installation and clears for all domains registered
	 * in the Realurl configuration.
	 *
	 * @param array $params
	 * @param t3lib_TCEmain $parent
	 * @return void
	 */
	public function clearCacheCmd($params, &$parent) {
		if ($parent->admin || is_object($parent->BE_USER) && $parent->BE_USER->getTSConfigVal('options.clearCache.pages')) {
			switch ($params['cacheCmd']) {
				case 'pages':
				case 'all':
					$realurlUrlFinder = t3lib_div::makeInstance('URL_Finder_RealURL_PathCache');
					$rootpages = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('pages.uid', 'pages, sys_template', '(is_siteroot = 1 OR (sys_template.root = 1 AND pages.uid = sys_template.pid AND NOT sys_template.deleted AND NOT sys_template.hidden)) AND NOT pages.hidden AND NOT pages.deleted', 'pages.uid');
					$realurlDomainFound = FALSE;
					if (count($rootpages) > 0) {
						foreach ($rootpages as $rootpage) {
							foreach ($realurlUrlFinder->getDomainsFromRootpageId($rootpage['uid']) as $domain) {
								$realurlDomainFound = TRUE;
								if ($domain === '_DEFAULT') {
									$this->clearCacheForUrl('.*');
								} else {
									$this->clearCacheForUrl('.*', $domain);
								}
							}
						}
					}
					if ($realurlDomainFound === FALSE) {
						$this->clearCacheForUrl('.*');
					}
				break;
			}
		}
	}

	/**
	 * Called when TYPO3 clears a list of uid's
	 *
	 * @param array $params
	 * @param t3lib_TCEmain $parent
	 * @return void
	 */
	public function clearCacheForListOfUids($params, &$parent) {
		$urlLocatorService = t3lib_div::makeInstance('URL_Finder_ServiceLocator');
		$urlLocatorService->injectURLFinder(t3lib_div::makeInstance('URL_Finder_RealURL_PathCache'));
		// @TODO: Implement a service location, that finds the correct Cache Manager instance depending on settings
		foreach ($params['pageIdArray'] as $uid) {
			foreach ($urlLocatorService->getUrlFromPageID($uid) as $url) {
				$this->clearCacheForUrl($url['pagepath'], $url['domain']);
			}
		}
	}

	/**
	 * @param string $url
	 * @param string $domain
	 * @return void
	 */
	protected function clearCacheForUrl($url, $domain = '') {
		if ($this->extConf['schedulerPurgeQueue']) {
			$table = 'tx_mocvarnish_purge_queue';
			$query = $GLOBALS['TYPO3_DB']->INSERTquery($table, array(
				'url' => $GLOBALS['TYPO3_DB']->quoteStr($url),
				'domain' => $GLOBALS['TYPO3_DB']->quoteStr($domain),
				'tstamp' => time(),
				'crdate' => time(),
				'cruser_id' => $GLOBALS['BE_USER']->user['uid']
			), $table);
			$GLOBALS['TYPO3_DB']->sql_query(str_replace('INSERT', 'INSERT IGNORE', $query));
		} else {
			$this->varnishCacheMgm->clearCacheForUrl($url, $domain);
		}
	}

}
?>
