<?php
namespace MOC\MocVarnish\Event;

/**
 * Event for purging a specific URL.
 *
 * @package MOC\MocVarnish\Event
 */
class PurgeUrlEvent implements PurgeEventInterface {

	/**
	 * @var string
	 */
	public $url = '';

	/**
	 * @var string
	 */
	public $domain = '';

	/**
	 * @param string $url The URL (relative) to clear cache for
	 * @param string $domain Specific domain to clear for. If left empty (default) all known domains will be cleared
	 */
	public function __construct($url, $domain = '') {
		$this->url = $url;
		$this->domain = $domain;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return 'Url ' . $this->domain . '/' . $this->url;
	}

}
