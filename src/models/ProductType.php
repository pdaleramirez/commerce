<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\models;

use craft\behaviors\FieldLayoutBehavior;
use craft\commerce\base\Model;
use craft\commerce\elements\Product;
use craft\commerce\elements\Variant;
use craft\commerce\fieldlayoutelements\VariantsField;
use craft\commerce\Plugin;
use craft\commerce\records\ProductType as ProductTypeRecord;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;
use craft\helpers\UrlHelper;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;
use craft\validators\HandleValidator;
use craft\validators\UniqueValidator;

/**
 * Product type model.
 * @method null setFieldLayout(FieldLayout $fieldLayout)
 * @method FieldLayout getFieldLayout()
 *
 * @property string $cpEditUrl
 * @property string $cpEditVariantUrl
 * @property FieldLayout $fieldLayout
 * @property mixed $productFieldLayout
 * @property array|ShippingCategory[]|int[] $shippingCategories
 * @property ProductTypeSite[] $siteSettings the product types' site-specific settings
 * @property TaxCategory[]|array|int[] $taxCategories
 * @property mixed $variantFieldLayout
 * @mixin FieldLayoutBehavior
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 2.0
 */
class ProductType extends Model
{
    /**
     * @var int ID
     */
    public $id;

    /**
     * @var string Name
     */
    public $name;

    /**
     * @var string Handle
     */
    public $handle;

    /**
     * @var bool Has dimension
     */
    public $hasDimensions;

    /**
     * @var bool Has variants
     */
    public $hasVariants;

    /**
     * @var bool Has variant title field
     */
    public $hasVariantTitleField = true;

    /**
     * @var string Variant title format
     * TODO: Rename to variantTitleFormat in 4.0
     */
    public $titleFormat = '{product.title}';

    /**
     * @var bool Has product title field?
     */
    public $hasProductTitleField = true;

    /**
     * @var string Product title format
     */
    public $productTitleFormat = '';

    /**
     * @var string SKU format
     */
    public $skuFormat;

    /**
     * @var string Description format
     */
    public $descriptionFormat;

    /**
     * @var string Line item format
     */
    public $lineItemFormat;

    /**
     * @var string Template
     */
    public $template;

    /**
     * @var  int Field layout ID
     */
    public $fieldLayoutId;

    /**
     * @var int Variant layout ID
     */
    public $variantFieldLayoutId;

    /**
     * @var string UID
     */
    public $uid;

    /**
     * @var TaxCategory[]
     */
    private $_taxCategories;

    /**
     * @var ShippingCategory[]
     */
    private $_shippingCategories;

    /**
     * @var ProductTypeSite[]
     */
    private $_siteSettings;


    /**
     * @return null|string
     */
    public function __toString()
    {
        return $this->handle;
    }

    /**
     * @inheritdoc
     */
    public function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['id', 'fieldLayoutId', 'variantFieldLayoutId'], 'number', 'integerOnly' => true];
        $rules[] = [['name', 'handle', 'titleFormat'], 'required'];
        $rules[] = [['name', 'handle', 'descriptionFormat'], 'string', 'max' => 255];
        $rules[] = [['handle'], UniqueValidator::class, 'targetClass' => ProductTypeRecord::class, 'targetAttribute' => ['handle'], 'message' => 'Not Unique'];
        $rules[] = [['handle'], HandleValidator::class, 'reservedWords' => ['id', 'dateCreated', 'dateUpdated', 'uid', 'title']];

        return $rules;
    }

    /**
     * @return string
     */
    public function getCpEditUrl(): string
    {
        return UrlHelper::cpUrl('commerce/settings/producttypes/' . $this->id);
    }

    /**
     * @return string
     */
    public function getCpEditVariantUrl(): string
    {
        return UrlHelper::cpUrl('commerce/settings/producttypes/' . $this->id . '/variant');
    }

    /**
     * Returns the product types's site-specific settings.
     *
     * @return ProductTypeSite[]
     */
    public function getSiteSettings(): array
    {
        if ($this->_siteSettings !== null) {
            return $this->_siteSettings;
        }

        if (!$this->id) {
            return [];
        }

        $this->setSiteSettings(ArrayHelper::index(Plugin::getInstance()->getProductTypes()->getProductTypeSites($this->id), 'siteId'));

        return $this->_siteSettings;
    }

    /**
     * Sets the product type's site-specific settings.
     *
     * @param ProductTypeSite[] $siteSettings
     */
    public function setSiteSettings(array $siteSettings)
    {
        $this->_siteSettings = $siteSettings;

        foreach ($this->_siteSettings as $settings) {
            $settings->setProductType($this);
        }
    }

    /**
     * @return ShippingCategory[]
     */
    public function getShippingCategories(): array
    {
        if ($this->_shippingCategories === null) {
            $this->_shippingCategories = Plugin::getInstance()->getShippingCategories()->getShippingCategoriesByProductTypeId($this->id);
        }

        return $this->_shippingCategories;
    }

    /**
     * @param int[]|ShippingCategory[] $shippingCategories
     */
    public function setShippingCategories($shippingCategories)
    {
        $categories = [];
        foreach ($shippingCategories as $category) {
            if (is_numeric($category)) {
                if ($category = Plugin::getInstance()->getShippingCategories()->getShippingCategoryById($category)) {
                    $categories[$category->id] = $category;
                }
            } else if ($category instanceof ShippingCategory) {
                // Make sure it exists
                if ($category = Plugin::getInstance()->getShippingCategories()->getShippingCategoryById($category->id)) {
                    $categories[$category->id] = $category;
                }
            }
        }

        $this->_shippingCategories = $categories;
    }

    /**
     * @return TaxCategory[]
     */
    public function getTaxCategories(): array
    {
        if ($this->_taxCategories === null) {
            $this->_taxCategories = Plugin::getInstance()->getTaxCategories()->getTaxCategoriesByProductTypeId($this->id);
        }

        return $this->_taxCategories;
    }

    /**
     * @param int[]|TaxCategory[] $taxCategories
     */
    public function setTaxCategories($taxCategories)
    {
        $categories = [];
        foreach ($taxCategories as $category) {
            if (is_numeric($category)) {
                if ($category = Plugin::getInstance()->getTaxCategories()->getTaxCategoryById($category)) {
                    $categories[$category->id] = $category;
                }
            } else {
                if ($category instanceof TaxCategory) {
                    // Make sure it exists.
                    if ($category = Plugin::getInstance()->getTaxCategories()->getTaxCategoryById($category->id)) {
                        $categories[$category->id] = $category;
                    }
                }
            }
        }

        $this->_taxCategories = $categories;
    }

    /**
     * @return FieldLayout
     */
    public function getProductFieldLayout(): FieldLayout
    {
        /** @var FieldLayoutBehavior $behavior */
        $behavior = $this->getBehavior('productFieldLayout');
        $fieldLayout = $behavior->getFieldLayout();

        // If this product type has variants, make sure the Variants field is in the layout somewhere
        if ($this->hasVariants && !$fieldLayout->isFieldIncluded('variants')) {
            $layoutTabs = $fieldLayout->getTabs();
            $variantTabName = Plugin::t('Variants');
            if (ArrayHelper::contains($layoutTabs, 'name', $variantTabName)) {
                $variantTabName .= ' ' . StringHelper::randomString(10);
            }
            $layoutTabs[] = new FieldLayoutTab([
                'name' => $variantTabName,
                'elements' => [
                    [
                        'type' => VariantsField::class,
                    ],
                ],
            ]);
            $fieldLayout->setTabs($layoutTabs);
        }

        return $fieldLayout;
    }

    /**
     * @return FieldLayout
     */
    public function getVariantFieldLayout(): FieldLayout
    {
        /** @var FieldLayoutBehavior $behavior */
        $behavior = $this->getBehavior('variantFieldLayout');
        return $behavior->getFieldLayout();
    }

    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            'productFieldLayout' => [
                'class' => FieldLayoutBehavior::class,
                'elementType' => Product::class,
                'idAttribute' => 'fieldLayoutId'
            ],
            'variantFieldLayout' => [
                'class' => FieldLayoutBehavior::class,
                'elementType' => Variant::class,
                'idAttribute' => 'variantFieldLayoutId'
            ],
        ];
    }
}
