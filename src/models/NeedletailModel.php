<?php
/**
 * Needletail plugin for Craft CMS 3.x
 *
 * Needletail Search and Index package for Craft 3.x
 *
 * @link      https://needletail.io
 * @copyright Copyright (c) 2019 Needletail
 */

namespace needletail\needletail\models;

use needletail\needletail\Needletail;

use Craft;
use craft\base\Model;

/**
 * NeedletailModel Model
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Needletail
 * @package   Needletail
 * @since     1.0.0
 */
class NeedletailModel extends Model
{
    const REGISTRATION_URL = 'https://needletail.io/register';

    // Public Properties
    // =========================================================================

    // Public Methods
    // =========================================================================

    public function getRegistrationUrl()
    {
        return self::REGISTRATION_URL;
    }
}
