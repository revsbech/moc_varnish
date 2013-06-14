<?php
namespace MOC\MocVarnish\Frontend\Authentication;

/**
 * Class FrontendUserAuthentication
 *
 * This class extends the similar class in the \TYPO3\CMS\Frontend\Authentication\
 * namespace.
 * It changes the property dontSetCookie to be TRUE by default. And manually call setSessionCookie
 * after calling storeSessionData (if there was a change to session data). The same goes for
 * createUserSession.
 *
 * @package MOC\MocVarnish\Frontend\Authentication
 */
class FrontendUserAuthentication extends \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication {

	/**
	 * @var boolean
	 */
	public $dontSetCookie = TRUE;

	/**
	 * @var boolean
	 */
	public $cookieIsSet = FALSE;

	/**
	 * @return void
	 */
	public function storeSessionData() {
		if ($this->sesData_change) {
			$this->setSessionCookie();
		}
		parent::storeSessionData();
	}

	/**
	 * @param array $tempuser
	 * @return void
	 */
	public function createUserSession($tempuser) {
		$this->setSessionCookie();
		parent::createUserSession($tempuser);
	}

	/**
	 * @return void
	 */
	protected function setSessionCookie() {
		if ($this->cookieIsSet === FALSE) {
			parent::setSessionCookie();
			$this->cookieIsSet = TRUE;
		}
	}
}