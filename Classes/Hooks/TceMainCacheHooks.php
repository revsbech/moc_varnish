<?php
namespace MOC\MocVarnish\Hooks;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Hook for clearing cache whenever TYPO3 TCE clears the internal cache
 *
 * @package MOC\MocVarnish\Hooks
 */
class TceMainCacheHooks {

	/**
	 * @var \MOC\MocVarnish\CacheManager
	 */
	protected $varnishCacheMgm;

	/**
	 * Constructor
	 */
	public function __construct() {
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

			// All this is done to make sure that Dependency injection works properly when this hook is called
		$configurationManager = $objectManager->get('TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface');
		$configuration = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::CONFIGURATION_TYPE_FRAMEWORK);
		$configuration['extensionName'] = 'moc_varnish';
		$configuration['pluginName'] = 'cache_clear';
		$bootstrap = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Core\Bootstrap');
		$bootstrap->initialize($configuration);

		$this->varnishCacheMgm = $objectManager->get('MOC\MocVarnish\CacheManager');
	}

	/**
	 * Purge the varnish cache when clearing all caches or clearing page content cache.
	 * Finds all rootpages of the installation and clears for all domains registered
	 * in the Realurl configuration.
	 *
	 * @param array $params
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $parent
	 * @return void
	 */
	public function clearCacheCmd($params, \TYPO3\CMS\Core\DataHandling\DataHandler $parent) {
		if ($parent->admin || is_object($parent->BE_USER) && $parent->BE_USER->getTSConfigVal('options.clearCache.pages')) {
			switch ($params['cacheCmd']) {
				case 'pages':
				case 'all':
					$this->varnishCacheMgm->clearCacheForUrl('.*');
					break;
				default:
			}
		}
	}

	/**
	 * Called when TYPO3 clears a list of uid's
	 *
	 * @param array $params
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $parent
	 * @return void
	 */
	public function clearCacheForListOfUids($params, \TYPO3\CMS\Core\DataHandling\DataHandler $parent) {
		foreach ($params['pageIdArray'] as $uid) {
			$this->varnishCacheMgm->clearCacheForPageUid($uid);
		}
	}

}