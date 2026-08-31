<?php
declare(strict_types=1);
namespace SaschaSchieferdecker\Instagram\Hooks\PageLayoutView;

use SaschaSchieferdecker\Instagram\Exception\ConfigurationException;
use TYPO3\CMS\Backend\Preview\PreviewRendererInterface;
use TYPO3\CMS\Backend\View\PageLayoutContext;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class AbstractPreviewRenderer
 */
abstract class AbstractPreviewRenderer implements PreviewRendererInterface
{
    /**
     * @var string
     */
    protected string $templatePath = 'EXT:instagram/Resources/Private/Templates/PreviewRenderer/';

    /**
     * Return the template file name (without .html extension) for the preview
     */
    abstract protected function getTemplateName(): string;

    public function renderPageModulePreviewHeader(PageLayoutContext $context): string
    {
        $record = $context->getRecord();
        return '<div id="element-tt_content-' . (int)$record['uid']
            . '" class="t3-ctype-identifier" data-ctype="' . htmlspecialchars((string)$record['CType']) . '"></div>';
    }

    public function renderPageModulePreviewContent(PageLayoutContext $context): string
    {
        $record = $context->getRecord();
        $templateFile = GeneralUtility::getFileAbsFileName(
            $this->templatePath . $this->getTemplateName() . '.html'
        );

        if (!is_file($templateFile)) {
            throw new ConfigurationException(
                'Expected template file for preview rendering is missing: ' . $this->templatePath . $this->getTemplateName() . '.html',
                1605299974
            );
        }

        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
        $standaloneView->getRenderingContext()->getTemplatePaths()->setTemplatePathAndFilename($templateFile);
        $standaloneView->assignMultiple([
            'data' => $record,
            'flexForm' => $this->getFlexForm($record),
        ]);
        return $standaloneView->render();
    }

    public function wrapPageModulePreview(
        string $previewHeader,
        string $previewContent,
        PageLayoutContext $context
    ): string {
        return $previewHeader . $previewContent;
    }

    protected function getFlexForm(array $record): array
    {
        $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
        return $flexFormService->convertFlexFormContentToArray($record['pi_flexform'] ?? '');
    }
}
