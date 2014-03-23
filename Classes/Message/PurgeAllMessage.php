<?php
namespace MOC\MocVarnish\Message;

use MOC\MocMessageQueue\Message\AbstractMessage;
use MOC\MocMessageQueue\Message\MessageInterface;

/**
 * Message queue message for clearing/purging a given TYPO3 page in Varnish.
 *
 * @package MOC\MocVarnish
 */
class PurgeAllMessage extends AbstractMessage implements MessageInterface {

	/**
	 * @var string
	 */
	public $domain = '';

	/**
	 * @param string $domapn Optional domain to purge for
	 */
	public function __construct($domain) {
		$this->pageUid = $pageUid;
	}

}