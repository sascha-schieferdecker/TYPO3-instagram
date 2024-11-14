<?php
declare(strict_types=1);
namespace SaschaSchieferdecker\Instagram\Hooks\PageLayoutView;

/**
 * Class Pi1PreviewRenderer
 */
class JsonPreviewRenderer extends AbstractPreviewRenderer
{
    /**
     * @var string
     */
    protected $cType = 'list';

    /**
     * @var string
     */
    protected $listType = 'instagram_json';
}
