<?php
/**
 * Needletail plugin for Craft CMS 3.x
 *
 * Needletail Search and Index package for Craft 3.x
 *
 * @link      https://needletail.io
 * @copyright Copyright (c) 2019 Needletail
 */

namespace needletail\needletail\twigextensions;

use Closure;
use needletail\needletail\Needletail;

use Craft;
use Twig\TwigFunction;

/**
 * Twig can be extended in many ways; you can add extra tags, filters, tests, operators,
 * global variables, and functions. You can even extend the parser itself with
 * node visitors.
 *
 * http://twig.sensiolabs.org/doc/advanced.html
 *
 * @author    Needletail
 * @package   Needletail
 * @since     1.0.0
 */
class NeedletailTwigExtension extends \Twig_Extension
{
    // Public Methods
    // =========================================================================

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'Needletail';
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('hash_get', [$this, 'hashGet']),
        ];
    }

    public function hashGet($array, $key, $default = null)
    {
        return Needletail::$plugin->hash->get($array, $key, $default);
    }
}
