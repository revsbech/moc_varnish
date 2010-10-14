<?php 
require_once(t3lib_extMgm::extPath('moc_varnish').'lib/URL/Finder.php');
require_once(t3lib_extMgm::extPath('moc_varnish').'lib/Varnish/CacheManager.php');
class tx_mocvarnish_tcemain_cachehooks {
	public function clearCacheForListOfUids($params,&$parent) {
		$service = new URL_Finder_ServiceLocator();
		$service->injectURLFinder(new URL_Finder_RealURL_PathCache());
			
		//@TODO: Implement a service location, that finds the correct Cache Manger instance depending on settings
		$varnishCacheMgm = new Varnish_CacheManager_CURLHTTP('http://localhost/');
		
		foreach($params['pageIdArray'] as $uid) {
			foreach($service->getUrlFromPageID($uid) as $url) {
				//@TODO: Make a setting where its possible to set a default domain if no domain was found.
				if(!$url['domain']) {
					//$url['domain'] = 'edntest.local';
				}
				
				$varnishCacheMgm->clearCacheForUrl($url['pagepath'],$url['domain']);
			}
		}
	}
}

?>