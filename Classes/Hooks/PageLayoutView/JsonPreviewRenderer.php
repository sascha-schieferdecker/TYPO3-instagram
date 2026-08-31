<?php
declare(strict_types=1);
namespace SaschaSchieferdecker\Instagram\Hooks\PageLayoutView;

/**
 * Class JsonPreviewRenderer
 */
class JsonPreviewRenderer extends AbstractPreviewRenderer
{
    protected function getTemplateName(): string
    {
        return 'InstagramJson';
    }
}
