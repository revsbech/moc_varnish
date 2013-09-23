<?php
namespace MOC\MocVarnish\Message;

use MOC\MocMessageQueue\Message\AbstractMessage;
use MOC\MocMessageQueue\Message\MessageInterface;

/**
 * @package MOC\MocVarnish
 */
class PurgePageUidMessage extends AbstractMessage implements MessageInterface {

	/**
	 * @var integer
	 */
	public $pageUid = 0;

	/**
	 * @param integer $pageUid
	 */
	public function __construct($pageUid) {
		$this->pageUid = $pageUid;
	}

}