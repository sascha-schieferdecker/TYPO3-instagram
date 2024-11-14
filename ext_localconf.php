<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(
    function () {
        /**
         * Include Frontend Plugins
         */
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Instagram',
            'Pi1',
            [
                \SaschaSchieferdecker\Instagram\Controller\ProfileController::class => 'show'
            ]
        );

        /**
         * Caching framework
         */
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['instagram'] ?? '') === false) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['instagram'] = [];
        }

        /**
         * ContentElementWizard
         */
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
            '@import "EXT:instagram/Configuration/TSConfig/ContentElementWizard.typoscript"'
        );
    }
);
