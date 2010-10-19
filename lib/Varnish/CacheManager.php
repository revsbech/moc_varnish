<?php 

interface Varnish_CacheMangerInterface {
	
	/**
	 * Clear cache for a given urls, possibly on a certains domain.
	 * The second paramater to the function, is the domain for which the URL should cleared.
	 * If left empty (the default), it will be cleared for all domain.
	 *  
	 * @param string $url	The URL to cleare cache for
	 * @param strong $domain	The domain for which the URL should is to be cleared. If left empty, means all (The defalt is empty).
	 */
	public function clearCacheForUrl($url,$domain="");
	
}

class Varnish_CacheManager_CURLHTTP implements Varnish_CacheMangerInterface {
	

	protected $clearQueue = array();
	/**
	 * 
	 * Create an instance of the CURLHTTP cachemanager. IT takes one parameter, the http address 
	 * (including http://) that the Varnish server is running on. If this parameter is specified
	 * This one is used, otherwise, the host of the URL that needs to cleared is used. 
	 *
	 * @param unknown_type $varnish
	 */
	public function __construct() {
	}
	
	public function clearCacheForUrl($url, $domain="", $scheme ="http://") {
		if($domain) {
			$path = $scheme.$domain.$url;
		} else {
			$path = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST').'/'.$url;
		}
		
		$this->clearQueue[] = $path;
	
		
	}
	
	public function excute() {
		//return false;
		$curl_handles = array();
		
		if(count($this->clearQueue) > 0) {
			$mh = curl_multi_init();
			foreach($this->clearQueue as $path) {
				
				$ch = $this->getCurlHandleForCacheClearing($path);
				$curl_handles[] = $ch;
				curl_multi_add_handle($mh, $ch);
				//print "Clearing cache for  ($path) <br />";
				//curl_exec($ch);		
			}

			$active = null;
		//execute the handles
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
				
			foreach($curl_handles as $ch) {
				curl_close($ch);
				curl_multi_remove_handle($mh, $ch);
			}
			
			curl_multi_close($mh);
			$this->clearQueue = array();
			
		}
		
	}
	protected function getCurlHandleForCacheClearing($url) {
		
		$curlHandle = curl_init();
		curl_setopt($curlHandle, CURLOPT_URL , $url);
		curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'PURGE');
		curl_setopt ($curlHandle, CURLOPT_HEADER, 0);
		curl_setopt ($curlHandle, CURLOPT_RETURNTRANSFER, 1);
		return $curlHandle;	
	}
	
	public function __destruct() {
		
		$this->excute();
	}
}

?>