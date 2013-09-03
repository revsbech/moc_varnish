<?php
namespace MOC\MocVarnish\Event;

/**
 * Event for purging specific pages
 *
 * @package MOC\MocVarnish\Event
 */
class PurgePageUidEvent implements PurgeEventInterface {

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