<?php

namespace needletail\needletail\services;

use craft\base\Component;
use craft\errors\MissingComponentException;
use craft\helpers\Component as ComponentHelper;
use needletail\needletail\base\ElementInterface;
use needletail\needletail\elements\Asset;
use needletail\needletail\elements\Category;
use needletail\needletail\elements\Entry;
use needletail\needletail\events\RegisterNeedletailElementsEvent;


class Elements extends Component
{
    // Constants
    // =========================================================================

    const EVENT_REGISTER_NEEDLETAIL_ELEMENTS = 'registerNeedletailElements';

    // Properties
    // =========================================================================

    private $_elements = [];

    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        foreach ($this->getRegisteredElements() as $elementClass) {
            $element = $this->createElement($elementClass);

            // Does this element exist in Craft right now?
            if (!class_exists($element::$class)) {
                continue;
            }

            $handle = $element::$class;

            $this->_elements[$handle] = $element;
        }
    }

    public function getRegisteredElement($handle)
    {
        if (isset($this->_elements[$handle])) {
            return $this->_elements[$handle];
        }
    }

    public function elementsList()
    {
        return array_map(function ($element) {
            return $element::$name;
        }, $this->_elements);
    }

    public function getRegisteredElements()
    {
        if (count($this->_elements)) {
            return $this->_elements;
        }

        $elements = [
            Asset::class,
            Category::class,
            Entry::class
        ];

        $event = new RegisterNeedletailElementsEvent([
            'elements' => $elements,
        ]);

        $this->trigger(self::EVENT_REGISTER_NEEDLETAIL_ELEMENTS, $event);

        return $event->elements;
    }

    public function createElement($config)
    {
        if (is_string($config)) {
            $config = ['type' => $config];
        }

        try {
            $element = ComponentHelper::createComponent($config, ElementInterface::class);
        } catch (MissingComponentException $e) {
            $config['errorMessage'] = $e->getMessage();
            $config['expectedType'] = $config['type'];
            unset($config['type']);

            $element = new MissingDataType($config);
        }

        return $element;
    }
}