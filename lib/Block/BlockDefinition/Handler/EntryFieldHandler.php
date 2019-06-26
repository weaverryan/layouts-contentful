<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Block\BlockDefinition\Handler;

use Netgen\Layouts\API\Values\Block\Block;
use Netgen\Layouts\Block\BlockDefinition\BlockDefinitionHandler;
use Netgen\Layouts\Block\DynamicParameters;
use Netgen\Layouts\Contentful\Service\Contentful;
use Netgen\Layouts\Parameters\ParameterBuilderInterface;
use Netgen\Layouts\Parameters\ParameterType;
use Symfony\Component\HttpFoundation\RequestStack;
use Throwable;

final class EntryFieldHandler extends BlockDefinitionHandler
{
    /**
     * @var \Netgen\Layouts\Contentful\Service\Contentful
     */
    private $contentful;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    public function __construct(Contentful $contentful, RequestStack $requestStack)
    {
        $this->contentful = $contentful;
        $this->requestStack = $requestStack;
    }

    public function buildParameters(ParameterBuilderInterface $builder): void
    {
        $builder->add('field_identifier', ParameterType\IdentifierType::class);
    }

    public function getDynamicParameters(DynamicParameters $params, Block $block): void
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if ($currentRequest === null) {
            return;
        }

        $contentfulEntry = $currentRequest->attributes->get('contentDocument');
        $params['content'] = $contentfulEntry;

        $field = null;

        try {
            /** @var callable $callable */
            $callable = [$contentfulEntry, 'get' . $block->getParameter('field_identifier')];
            $field = call_user_func($callable);
        } catch (Throwable $t) {
            // Do nothing
        }

        $fieldType = $this->getFieldType($field);

        if ($fieldType === 'dynamicentry') {
            $params['field_value'] = $this->contentful->loadContentfulEntry($field->getSpace()->getId() . '|' . $field->getId());
            $params['field_type'] = 'entry';
        } elseif ($fieldType === 'array') {
            $fieldValues = [];

            foreach ($field as $innerField) {
                $innerFieldType = $this->getFieldType($innerField);
                if ($innerFieldType === 'dynamicentry') {
                    $fieldValues['entry'] = $this->contentful->loadContentfulEntry($innerField->getSpace()->getId() . '|' . $innerField->getId());
                } elseif (is_string($innerFieldType)) {
                    $fieldValues[$innerFieldType] = $innerField;
                }
            }

            $params['field_value'] = $fieldValues;
            $params['field_type'] = $fieldType;
        } elseif ($fieldType !== null) {
            $params['field_value'] = $field;
            $params['field_type'] = $fieldType;
        }
    }

    public function isContextual(Block $block): bool
    {
        return true;
    }

    /**
     * Returns the field type of the provided field.
     *
     * @param mixed $field
     *
     * @return string|null
     */
    private function getFieldType($field): ?string
    {
        if ($field === null) {
            return null;
        }

        $fieldType = gettype($field);
        if ($fieldType === 'object') {
            $classNameArray = explode('\\', get_class($field));
            if (count($classNameArray) === 0) {
                return null;
            }

            $fieldType = mb_strtolower($classNameArray[count($classNameArray) - 1]);
        }

        return $fieldType;
    }
}
