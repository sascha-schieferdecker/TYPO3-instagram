<?php
declare(strict_types=1);
namespace SaschaSchieferdecker\Instagram\Hooks\PageLayoutView;

/**
 * Class Pi1PreviewRenderer
 */
class Pi1PreviewRenderer extends AbstractPreviewRenderer
{
    protected function getTemplateName(): string
    {
        return 'InstagramPi1';
    }
}
