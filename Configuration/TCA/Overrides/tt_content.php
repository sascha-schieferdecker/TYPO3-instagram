<?php
defined('TYPO3') || die();

/**
 * Register Plugins
 */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin('instagram', 'Pi1', 'Instagram');
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin('instagram', 'Json', 'Instagram JSON');

/**
 * Include Flexform
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', '--div--;Configuration,pi_flexform,', 'instagram_pi1', 'after:subheader');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:instagram/Configuration/FlexForms/FlexFormPi1.xml',
    'instagram_pi1'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', '--div--;Configuration,pi_flexform,', 'instagram_json', 'after:subheader');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:instagram/Configuration/FlexForms/FlexFormJson.xml',
    'instagram_json'
);
