<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Parser;

use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;

/**
 * @internal
 */
trait AttributeGroupTraverser {

    private function findAttribute(string $attributeType, AttributeGroup... $attributeGroups) : ?Attribute {
        foreach ($attributeGroups as $attributeGroup) {
            foreach ($attributeGroup->attrs as $attribute) {
                if ($attribute->name->toString() === $attributeType) {
                    return $attribute;
                }
            }
        }

        return null;
    }
}