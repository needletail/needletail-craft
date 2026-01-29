<?php

namespace needletail\needletail\base;

use craft\base\Component;
use needletail\needletail\models\BucketModel;

abstract class Element extends Component
{
    /**
     * @var string
     */
    public static $name = '';

    /**
     * The Craft element class this wrapper targets.
     *
     * @var string
     */
    public static $class = '';

    public $element;

    public $bucket;

    // Public Methods
    // =========================================================================

    public function getName()
    {
        return $this::$name;
    }

    public function getClass()
    {
        return get_class($this);
    }

    public function getElementClass()
    {
        return $this::$class;
    }

    public function includesElement(BucketModel $bucket, \craft\base\ElementInterface $element): bool
    {
        if (!is_a($element, $this::$class)) {
            return false;
        }

        // Respect the bucket's selected site (if any)
        if ($bucket->siteId && isset($element->siteId) && (int)$element->siteId !== (int)$bucket->siteId) {
            return false;
        }

        try {
            $query = $this->getQuery($bucket, ['id' => $element->id]);

            if (method_exists($query, 'exists')) {
                return (bool)$query->exists();
            }

            return (int)$query->count() > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Implemented by element wrappers.
     *
     * @param BucketModel $bucket
     * @param array $params
     * @return \craft\elements\db\ElementQueryInterface
     */
    abstract public function getQuery(BucketModel $bucket, $params = []);

    /**
     * @param BucketModel $bucket
     * @param array $params
     * @return \craft\elements\db\ElementQueryInterface[]
     */
    public function getQueries(BucketModel $bucket, array $params = []): array
    {
        return [$this->getQuery($bucket, $params)];
    }

    public function shouldIndexElement(BucketModel $bucket, \craft\base\ElementInterface $element): bool
    {
        // Respect the bucket's selected site (if any)
        if ($bucket->siteId && isset($element->siteId) && (int)$element->siteId !== (int)$bucket->siteId) {
            return false;
        }

        if (!$element->getUrl()) {
            return false;
        }

        // Keep this deliberately strict: only index elements that Craft considers publicly visible.
        return in_array($element->getStatus(), ['live', 'enabled'], true);
    }

    public function parseAttribute(\craft\base\ElementInterface $element, $handle, $data)
    {
        // Find the class to deal with the attribute
        $name = 'parse' . ucwords($handle);

        // Set a default handler for non-specific attribute classes
        if (!method_exists($this, $name)) {
            try {
                if ($element instanceof \yii\base\Component && !$element->canGetProperty($handle)) {
                    return null;
                }

                return $element->{$handle};
            } catch (\Throwable $e) {
                // If an attribute isn't supported on this element type (e.g. "filename" on entries),
                // don't fail the entire indexing run.
                return null;
            }
        }

        $parsedValue = $this->$name($element, $data);

        return $parsedValue;
    }

    public function parseId($element, $data)
    {
        return (int) $element->id;
    }

    public function parseAuthorId($element, $data)
    {
        return $this->parseAnInteger($element->authorId);
    }

    public function parsePostDate($element, $data)
    {
        return $this->parseADateTimeValue($element->postDate);
    }

    public function parseExpiryDate($element, $data)
    {
        return $this->parseADateTimeValue($element->expiryDate);
    }

    public function parseDateCreated($element, $data)
    {
        return $this->parseADateTimeValue($element->dateCreated);
    }

    public function parseDateModified($element, $data)
    {
        return $this->parseADateTimeValue($element->dateModified);
    }

    public function parseDateUpdated($element, $data)
    {
        return $this->parseADateTimeValue($element->dateUpdated);
    }

    public function parseElementType($element, $data)
    {
        return get_class($element);
    }

    public function parseADateTimeValue(?\DateTime $date = null) {
        if ( ! $date )
            return $date;

        return $date->format(\DateTime::ATOM);
    }

    public function parseAnInteger($string)
    {
        return (int) $string;
    }
}
