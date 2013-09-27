<?php
namespace MOC\MocVarnish\Message;

use MOC\MocMessageQueue\Message\AbstractMessage;
use MOC\MocMessageQueue\Message\MessageInterface;

/**
 * Message queue message for clearing/purging a specific URL in Varnish.
 *
 * @package MOC\MocVarnish
 */
class PurgeUrlMessage extends AbstractMessage implements MessageInterface {

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

}