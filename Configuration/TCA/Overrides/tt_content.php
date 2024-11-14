<?php
defined('TYPO3') || die();

/**
 * Register Plugins
 */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin('instagram', 'Pi1', 'Instagram');
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin('instagram', 'Json', 'Instagram JSON');

/**
 * Disable not needed fields in tt_content
 */
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['instagram_pi1'] = 'select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['instagram_json'] = 'select_key,pages,recursive';

/**
 * Include Flexform
 */
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['instagram_pi1'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'instagram_pi1',
    'FILE:EXT:instagram/Configuration/FlexForms/FlexFormPi1.xml'
);
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['instagram_json'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'instagram_json',
    'FILE:EXT:instagram/Configuration/FlexForms/FlexFormJson.xml'
);
