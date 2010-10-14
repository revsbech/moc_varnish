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
	
	protected $varnishURL = 'http://localhost/';

	protected $clearQueue = array();
	/**
	 * 
	 * Create an instance of the CURLHTTP cachemanager. IT takes one parameter, the http address 
	 * (including http://) that the Varnish server is running on. If this parameter is specified
	 * This one is used, otherwise, the host of the URL that needs to cleared is used. 
	 *
	 * @param unknown_type $varnish
	 */
	public function __construct($varnish) {
		$this->varnishURL = $url;
		$this->varnishURL = 'http://localhost/';
	}
	
	public function clearCacheForUrl($url, $domain="") {
		if($domain) {
			$regex = "req.http.host == $domain && req.url ~ $url";
		} else {
			$regex = "req.url ~ $url";
		}
		
		$this->clearQueue[] = $regex;
	
		//print "Clearing cache for /$url on domain $domain ($regex)<br />";
	}
	
	public function excute() {
		return false;
		$curl_handles = array();
		if(count($this->clearQueue) > 0) {
			$mh = curl_multi_init();
			foreach($this->clearQueue as $regex) {
				$ch = $this->getCurlHandleForCacheClearing($regex);
				$curl_handles[] = $ch;
				curl_multi_add_handle($mh, $ch);
				//curl_exec($ch);
				print "Adding: ".$regex;
			}
			/*
			$stil_running=true;
			while($stil_running) {
				curl_multi_exec($mh, $still_running);
				
			}
			*/
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
	protected function getCurlHandleForCacheClearing($regex) {
		
		$curlHandle = curl_init();
		curl_setopt($curlHandle, CURLOPT_URL , $this->varnishURL);
		curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'PURGE');
		curl_setopt ($curlHandle, CURLOPT_HEADER, 0);
		curl_setopt ($curlHandle, CURLOPT_RETURNTRANSFER, 1);
		//@TODO: Make this configurable
		curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array('X-Varnish-purge: ' . $regex));
//		$content = curl_exec($curlHandle);
//		curl_close($curlHandle);	
		return $curlHandle;	
	}
	
	public function __destruct() {
		
		$this->excute();
	}
}

?>