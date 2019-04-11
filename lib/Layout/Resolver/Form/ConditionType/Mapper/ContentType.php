<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Layout\Resolver\Form\ConditionType\Mapper;

use Netgen\Layouts\Contentful\Service\Contentful;
use Netgen\Layouts\Layout\Resolver\Form\ConditionType\Mapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

final class ContentType extends Mapper
{
    /**
     * @var \Netgen\Layouts\Contentful\Service\Contentful
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
            'choices' => $this->contentful->getSpacesAndContentTypesAsChoices(),
            'multiple' => true,
        ];
    }
}
