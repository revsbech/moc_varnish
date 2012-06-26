<?php
class ux_tx_felogin_pi1 extends tx_felogin_pi1 {

	/**
	 * The main method of the plugin
	 *
	 * @param string $content: The PlugIn content
	 * @param array $conf: The PlugIn configuration
	 * @return string The content that is displayed on the website
	 */
	public function main($content, $conf) {
		$content = parent::main($content, $conf);

			// Process the redirect (without cookie warning)
		if (($this->logintype === 'login' || $this->logintype === 'logout') && $this->redirectUrl && !$this->noRedirect) {
				// Add hook for extra processing before redirect
			if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['beforeRedirect']) && is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['beforeRedirect'])) {
				$_params = array(
					'loginType'   => $this->logintype,
					'redirectUrl' => &$this->redirectUrl
				);
				foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['beforeRedirect'] as $_funcRef) {
					if ($_funcRef) {
						t3lib_div::callUserFunction($_funcRef, $_params, $this);
					}
				}
			}
			t3lib_utility_Http::redirect($this->redirectUrl);
		}

		return $content;
	}

}