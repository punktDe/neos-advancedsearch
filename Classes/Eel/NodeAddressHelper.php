<?php
declare(strict_types=1);

namespace PunktDe\Neos\AdvancedSearch\Eel;

use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAddress;
use Neos\Eel\ProtectedContextAwareInterface;

/**
 * Eel helper to convert a Content Repository Node to a NodeAddress JSON string.
 * Used for passing context to the SuggestController (NodeAddress-based API).
 */
class NodeAddressHelper implements ProtectedContextAwareInterface
{
    public function toNodeAddressJson(Node $node): string
    {
        $address = NodeAddress::fromNode($node);
        return $address->toJson();
    }

    /**
     * All methods are considered safe
     */
    public function allowsCallOfMethod($methodName): bool
    {
        return true;
    }
}
