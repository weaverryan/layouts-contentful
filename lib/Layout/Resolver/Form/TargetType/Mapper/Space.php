<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Contentful\Layout\Resolver\Form\TargetType\Mapper;

use Netgen\BlockManager\Contentful\Service\Contentful;
use Netgen\BlockManager\Layout\Resolver\Form\TargetType\Mapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

final class Space extends Mapper
{
    /**
     * @var \Netgen\BlockManager\Contentful\Service\Contentful
     */
    private $contentful;

    public function __construct(Contentful $contentful)
    {
        $this->contentful = $contentful;
    }

    public function getFormType(): string
    {
        return ChoiceType::class;
    }

    public function getFormOptions(): array
    {
        return [
            'choices' => $this->contentful->getSpacesAsChoices(),
        ];
    }
}
