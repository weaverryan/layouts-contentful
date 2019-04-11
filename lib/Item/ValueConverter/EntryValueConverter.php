<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Item\ValueConverter;

use Netgen\Layouts\Contentful\Entity\ContentfulEntry;
use Netgen\Layouts\Item\ValueConverterInterface;

final class EntryValueConverter implements ValueConverterInterface
{
    public function supports(object $object): bool
    {
        return $object instanceof ContentfulEntry;
    }

    public function getValueType(object $object): string
    {
        return 'contentful_entry';
    }

    /**
     * @param \Netgen\Layouts\Contentful\Entity\ContentfulEntry $object
     *
     * @return int|string
     */
    public function getId(object $object)
    {
        return $object->getId();
    }

    /**
     * @param \Netgen\Layouts\Contentful\Entity\ContentfulEntry $object
     *
     * @return int|string
     */
    public function getRemoteId(object $object)
    {
        return $object->getId();
    }

    /**
     * @param \Netgen\Layouts\Contentful\Entity\ContentfulEntry $object
     */
    public function getName(object $object): string
    {
        return $object->getName();
    }

    /**
     * @param \Netgen\Layouts\Contentful\Entity\ContentfulEntry $object
     */
    public function getIsVisible(object $object): bool
    {
        return $object->getIsPublished();
    }

    public function getObject(object $object): object
    {
        return $object;
    }
}
