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
            ],
            [],
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
        );
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'Instagram',
            'Json',
            [
                \SaschaSchieferdecker\Instagram\Controller\ProfileController::class => 'json'
            ],
            [],
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
        );

        /**
         * Caching framework
         */
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['instagram'] ?? '') === false) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['instagram'] = [];
        }
    }
);
