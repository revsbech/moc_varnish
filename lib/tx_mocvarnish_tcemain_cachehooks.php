<?php
require_once(t3lib_extMgm::extPath('moc_varnish') . 'lib/URL/Finder.php');
require_once(t3lib_extMgm::extPath('moc_varnish') . 'lib/Varnish/CacheManager.php');

class tx_mocvarnish_tcemain_cachehooks {

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
		if ($parent->admin || $parent->BE_USER->getTSConfigVal('options.clearCache.pages')) {
			switch ($params['cacheCmd']) {
				case 'pages':
				case 'all':
					$varnishCacheMgm = new Varnish_CacheManager_CURLHTTP();
					$realurlUrlFinder = new URL_Finder_RealURL_PathCache();
					$rootpages = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('pages.uid', 'pages, sys_template', '(is_siteroot = 1 OR (sys_template.root = 1 AND pages.uid = sys_template.pid AND NOT sys_template.deleted AND NOt sys_template.hidden)) AND NOT pages.hidden AND NOT pages.deleted', 'pages.uid');
					foreach ($rootpages as $rootpage) {
						foreach ($realurlUrlFinder->getDomainsFromRootpageId($rootpage['uid']) as $domain) {
							if ($domain !== '_DEFAULT') {
								$varnishCacheMgm->clearCacheForUrl('.*', $domain);
							}
						}
					}
					$varnishCacheMgm->clearCacheForUrl('.*');
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
		$urlLocatorService = new URL_Finder_ServiceLocator();
		$urlLocatorService->injectURLFinder(new URL_Finder_RealURL_PathCache());

		// @TODO: Implement a service location, that finds the correct Cache Manager instance depending on settings
		$varnishCacheMgm = new Varnish_CacheManager_CURLHTTP();
		foreach ($params['pageIdArray'] as $uid) {
			foreach ($urlLocatorService->getUrlFromPageID($uid) as $url) {
				$varnishCacheMgm->clearCacheForUrl($url['pagepath'], $url['domain']);
			}
		}
	}

}
?>