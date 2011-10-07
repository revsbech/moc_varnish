<?php
/**
 *
 * The class overrides the default dontSetCookie value of tslib_feUserAuth. This way a new cookie is never
 * set unless explicitly asked for.
 * It changes the property dontSetCookie to be TRUE by default. And manually call setSessionCookie
 * after calling storeSessionData (if there was a change to session data). The same goes for
 * createUserSession.
 *
 */
class ux_tslib_feuserAuth extends tslib_feUserAuth {
	public $dontSetCookie = TRUE;

	public $cookieIsSet = FALSE;

	function storeSessionData() {
		if ($this->sesData_change) {
			$this->setSessionCookie();
		}
		parent::storeSessionData();
	}

	function createUserSession($tempuser) {
		$this->setSessionCookie();
		parent::createUserSession($tempuser);
	}

	protected function setSessionCookie() {
		if ($this->cookieIsSet === FALSE) {
			parent::setSessionCookie();
			$this->cookieIsSet = TRUE;
		}
	}
}
