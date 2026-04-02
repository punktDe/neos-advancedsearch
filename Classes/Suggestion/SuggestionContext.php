<?php
declare(strict_types=1);

namespace PunktDe\Neos\AdvancedSearch\Suggestion;

use Neos\Flow\Annotations as Flow;
use Flowpack\SearchPlugin\Suggestion\SuggestionContext as FlowpackSuggestionContext;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;
use Neos\Neos\Domain\SubtreeTagging\NeosSubtreeTag;

class SuggestionContext extends FlowpackSuggestionContext
{
    public function buildForIndex(Node $node): self
    {
        $this->contextValues = [
            'siteName' => $this->getSiteName($node),
            'workspace' => $node->workspaceName->value,
        ];
        $contentRepository = $this->contentRepositoryRegistry->get($node->contentRepositoryId);

        if ($node->tags->contain(NeosSubtreeTag::disabled()) ||
            (bool)$node->getProperty('metaRobotsNoindex') === true ||
            $contentRepository->getNodeTypeManager()->getNodeType($node->nodeTypeName)->isOfType('PunktDe.Neos.AdvancedSearch:Mixin.HiddenFromInternalSearch') ||
            (bool)$node->getProperty('internalSearchNoIndex') === true
        ) {
            $this->contextValues['isHidden'] = 'hidden';
        } else {
            $this->contextValues['isHidden'] = 'visible';
        }

        return $this;
    }
}
