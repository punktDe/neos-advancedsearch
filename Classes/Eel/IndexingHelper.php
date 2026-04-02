<?php
declare(strict_types=1);

namespace PunktDe\Neos\AdvancedSearch\Eel;

use Flowpack\SearchPlugin\EelHelper\SuggestionIndexHelper;
use Flowpack\SearchPlugin\Exception;
use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\Neos\Domain\SubtreeTagging\NeosSubtreeTag;
use Neos\Eel\ProtectedContextAwareInterface;
use PunktDe\Neos\AdvancedSearch\NodeTypeDefinitionInterface;
use PunktDe\Neos\AdvancedSearch\TextTokenizer;
use Neos\ContentRepositoryRegistry\ContentRepositoryRegistry;

class IndexingHelper implements ProtectedContextAwareInterface
{
    public function __construct(
        private readonly TextTokenizer         $textTokenizer,
        private readonly SuggestionIndexHelper $suggestionIndexHelper,
        private readonly ContentRepositoryRegistry $contentRepositoryRegistry,
    ) {
    }

    /**
     * @param Node $node
     * @param string[] $properties
     * @return string
     */
    public function indexCompletionIfSearchable(Node $node, array $properties): string
    {
        $contentRepository = $this->contentRepositoryRegistry->get($node->contentRepositoryId);
        if ($contentRepository->getNodeTypeManager()->getNodeType($node->nodeTypeName)->isOfType(NodeTypeDefinitionInterface::MIXIN_HIDDEN_FROM_INTERNAL_SEARCH)) {
            return '';
        }

        return implode(' ', $this->stopWordFilteredTokenize(implode(' ', $this->extractNodeProperties($properties, $node)), $node));
    }

    /**
     * @param Node $node
     * @param string[] $properties
     * @param int $weight
     * @return string[]
     * @throws Exception
     */
    public function indexSuggestionIfSearchable(Node $node, array $properties, int $weight = 1): array
    {
        $contentRepository = $this->contentRepositoryRegistry->get($node->contentRepositoryId);
        if ($node->tags->contain(NeosSubtreeTag::disabled()) || $contentRepository->getNodeTypeManager()->getNodeType($node->nodeTypeName)->isOfType(NodeTypeDefinitionInterface::MIXIN_HIDDEN_FROM_INTERNAL_SEARCH)) {
            return [];
        }
        return $this->suggestionIndexHelper->build($this->stopWordFilteredTokenize(implode(' ', $this->extractNodeProperties($properties, $node)), $node), $weight);
    }

    /**
     * @param string[] $properties
     * @param Node $node
     * @return string[]
     */
    private function extractNodeProperties(array $properties, Node $node): array
    {
        $completionContent = [];

        foreach ($properties as $propertyName) {
            if (is_string($node->getProperty($propertyName))) {
                $completionContent[] = strip_tags($node->getProperty($propertyName));
            }
        }
        return $completionContent;
    }

    /**
     * @param string $input
     * @param Node $node
     * @return string[]
     */
    public function stopWordFilteredTokenize(string $input, Node $node): array
    {
        $languageCoord = $node->dimensionSpacePoint->coordinates['language'] ?? '';
        $language = is_array($languageCoord) ? current($languageCoord) : $languageCoord;
        if (empty($language)) {
            return [];
        }

        return array_values($this->textTokenizer->tokenize($input, $language));
    }

    /**
     * All methods are considered safe
     *
     * @param string $methodName
     * @return boolean
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
