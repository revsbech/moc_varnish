<?php
namespace MOC\MocVarnish;

/**
 * Interface for URL Locator service
 *
 * All services which are able to translate a pageUid to a URL should implement this interface.
 * Examples are RealURL pagecache, CoolURI.
 *
 * @package MOC\MocVarnish
 */
interface UrlLocatorServiceInterface {

	/**
	 * @param integer $uid
	 * @return array An array of all found URL for this page id. Each array contains the pagepath and domain
	 */
	public function getURLFromPageID($uid);

}