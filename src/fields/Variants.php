<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\fields;

use craft\commerce\elements\Variant;
use craft\commerce\gql\arguments\elements\Variant as VariantArguments;
use craft\commerce\gql\interfaces\elements\Variant as VariantInterface;
use craft\commerce\gql\resolvers\elements\Variant as VariantResolver;
use craft\commerce\Plugin;
use craft\fields\BaseRelationField;
use GraphQL\Type\Definition\Type;

/**
 * Class Variant Field
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 2.0
 */
class Variants extends BaseRelationField
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Plugin::t('Commerce Variants');
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return Plugin::t('Add a variant');
    }

    /**
     * @inheritdoc
     * @since 3.1.4
     */
    public function getContentGqlType()
    {
        return [
            'name' => $this->handle,
            'type' => Type::listOf(VariantInterface::getType()),
            'args' => VariantArguments::getArguments(),
            'resolve' => VariantResolver::class . '::resolve',
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function elementType(): string
    {
        return Variant::class;
    }
}
