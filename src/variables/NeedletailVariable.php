<?php
/**
 * Needletail plugin for Craft CMS 3.x
 *
 * Needletail Search and Index package for Craft 3.x
 *
 * @link      https://needletail.io
 * @copyright Copyright (c) 2019 Needletail
 */

namespace needletail\needletail\variables;

use craft\helpers\DateTimeHelper;
use craft\helpers\UrlHelper;
use needletail\needletail\Needletail;

use Craft;
use yii\di\ServiceLocator;

/**
 * Needletail Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.needletail }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    Needletail
 * @package   Needletail
 * @since     1.0.0
 */
class NeedletailVariable extends ServiceLocator
{
    // Public Methods
    // =========================================================================

    /**
     * Get the plugin name
     *
     * @return string
     */
    public function getPluginName()
    {
        return Needletail::$plugin->buckets->getName();
    }

    public function getBucketPrefix()
    {
        return Needletail::$plugin->settings->getBucketPrefix();
    }

    public function bucket($handle, $includingPrefix = false)
    {
        return Needletail::$plugin->buckets->getByHandle($handle, $includingPrefix);
    }

    public function publicKey()
    {
        return Needletail::$plugin->settings->getApiReadKey();
    }

    public function getCpTabs()
    {
        return [
            'buckets' => [ 'label' => Craft::t('needletail', 'Buckets'), 'url' => UrlHelper::cpUrl('needletail/buckets') ],
            'help' => [ 'label' => Craft::t('needletail', 'Help'), 'url' => UrlHelper::cpUrl('needletail/help') ],
        ];
    }

    public function getSelectOptions($options, $label = 'name', $index = 'id', $includeNone = true)
    {
        $values = [];

        if ($includeNone) {
            if (is_string($includeNone)) {
                $values[''] = $includeNone;
            } else {
                $values[''] = 'None';
            }
        }

        if (is_array($options)) {
            foreach ($options as $key => $value) {
                $values[$value[$index]] = $value[$label];
            }
        }

        return $values;
    }

    //
    // Fields + Field Mapping
    //

    public function formatDateTime($dateTime)
    {
        return DateTimeHelper::toDateTime($dateTime);
    }

    public function fields()
    {
        return Needletail::$plugin->fields;
    }

    //
    // Helper functions for element fields in getting their inner-element field layouts
    //

    public function getElementLayoutByField($type, $field)
    {
        $source = null;

        if ($type === 'craft\fields\Assets') {
            $source = $this->getAssetSourcesByField($field)[0] ?? null;
        } else if ($type === 'craft\fields\Categories') {
            $source = $this->getCategorySourcesByField($field) ?? null;
        } else if ($type === 'craft\fields\Entries') {
            $section = $this->getEntrySourcesByField($field)[0] ?? null;

            if ($section) {
                $source = Craft::$app->sections->getEntryTypeById($section->id);
            }
        } else if ($type === 'craft\fields\Tags') {
            $source = $this->getCategorySourcesByField($field) ?? null;
        }

        if (!$source || !$source->fieldLayoutId) {
            return;
        }

        return Craft::$app->fields->getFieldsByLayoutId($source->fieldLayoutId);
    }

    public function getUserLayoutByField($field)
    {
        $layoutId = Craft::$app->fields->getLayoutByType(UserElement::class)->id;

        if (!$layoutId) {
            return null;
        }

        return Craft::$app->fields->getFieldsByLayoutId($layoutId);
    }


    //
    // Helper functions for element fields to get their first source. This is tricky as some elements
    // support multiple sources (Entries, Users), whilst others can only have one (Tags, Categories)
    //

    public function getAssetSourcesByField($field)
    {
        $sources = [];

        if (!$field) {
            return;
        }

        if (is_array($field->sources)) {
            foreach ($field->sources as $source) {
                list($type, $uid) = explode(':', $source);

                $sources[] = Craft::$app->volumes->getVolumeByUid($uid);
            }
        } else if ($field->sources === '*') {
            $sources = Craft::$app->volumes->getAllVolumes();
        }

        return $sources;
    }

    public function getCategorySourcesByField($field)
    {
        if (!$field) {
            return;
        }

        list($type, $uid) = explode(':', $field->source);

        return Craft::$app->categories->getGroupByUid($uid);
    }

    public function getEntrySourcesByField($field)
    {
        $sources = [];

        if (!$field) {
            return;
        }

        if (is_array($field->sources)) {
            foreach ($field->sources as $source) {
                if ($source == 'singles') {
                    foreach (Craft::$app->sections->getAllSections() as $section) {
                        if ($section->type == 'single') {
                            $sources[] = $section;
                        }
                    }
                } else {
                    list($type, $uid) = explode(':', $source);

                    $sources[] = Craft::$app->sections->getSectionByUid($uid);
                }
            }
        } else if ($field->sources === '*') {
            $sources = Craft::$app->sections->getAllSections();
        }

        return $sources;
    }

    public function getTagSourcesByField($field)
    {
        if (!$field) {
            return;
        }

        list($type, $uid) = explode(':', $field->source);

        return Craft::$app->tags->getTagGroupByUid($uid);
    }

    public function supportedSubField($class)
    {
        $supportedSubFields = [
            'craft\fields\Checkboxes',
            'craft\fields\Color',
            'craft\fields\Date',
            'craft\fields\Dropdown',
            'craft\fields\Lightswitch',
            'craft\fields\Multiselect',
            'craft\fields\Number',
            'craft\fields\PlainText',
            'craft\fields\PositionSelect',
            'craft\fields\Radio',
            'craft\fields\Redactor',
        ];

        return in_array($class, $supportedSubFields);
    }
}
