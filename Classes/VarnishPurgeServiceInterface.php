<?php
namespace MOC\MocVarnish;

/**
 * Interface for Varnish purge service
 *
 * All services which are capable of purging og banning specific URL's in varnish must
 * implement this interface. Examples of implementation could be CurlHTTPPurge and
 * command line.
 *
 * @package MOC\MocVarnish
 */
interface VarnishPurgeServiceInterface {

	/**
	 * Clear cache for a given url on a specific domain
	 *
	 * @param string $url The URL to cleare cache for
	 * @param string $domain The domain for which the URL should is to be cleared.
	 * @return void
	 */
	public function clearCacheForUrl($url, $domain);

}