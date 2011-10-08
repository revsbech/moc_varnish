<?php

require_once(t3lib_extMgm::extPath('moc_varnish').'lib/URL/Finder.php');
require_once(t3lib_extMgm::extPath('moc_varnish').'lib/Varnish/CacheManager.php');

class tx_mocvarnish_tcemain_cachehooks {

	/**
	 * Clearing the cache based on a page being updated
	 *
	 * @param	array		$params TODO
	 * @param	TODO		$parent TODO
	 * @return	void
	 */
	public function clearCacheCmd($params,&$parent) {

		if ($parent->admin || $parent->BE_USER->getTSConfigVal('options.clearCache.pages')) {
			switch($params['cacheCmd']) {
				case 'pages':
				case 'all':
					$varnishCacheMgm = new Varnish_CacheManager_CURLHTTP();
					$varnishCacheMgm->clearCacheForUrl('.*');
				break;
			}
		}
	}

	/**
	 * Called when TYPO3 clears a list of uid's
	 *
	 * @param	unknown_type	$params
	 * @param	unknown_type	$parent
	 * @return	void
	 */
	public function clearCacheForListOfUids($params,&$parent) {

		$urlLocatorService = new URL_Finder_ServiceLocator();
		$urlLocatorService->injectURLFinder(new URL_Finder_RealURL_PathCache());

		// @TODO: Implement a service location, that finds the correct Cache Manger instance depending on settings
		$varnishCacheMgm = new Varnish_CacheManager_CURLHTTP();

		foreach ($params['pageIdArray'] as $uid) {
			foreach ($urlLocatorService->getUrlFromPageID($uid) as $url) {
				// @TODO: Make a setting where its possible to set a default domain if no domain was found.
				if (!$url['domain']) {
					// Skip clearing the cache if no domain was found.
					continue;
				}

				$varnishCacheMgm->clearCacheForUrl($url['pagepath'],$url['domain']);
			}
		}

	}
}

?>
