<?php

namespace needletail\needletail\fields;

use craft\elements\User;

class Users extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Users';
    public static $class = 'craft\fields\Users';
    public static $elementType = 'craft\elements\User';


    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'needletail/_includes/fields/users';
    }


    // Public Methods
    // =========================================================================

    public function parseField()
    {
        $query = $this->element->getFieldValue($this->fieldHandle);

        return array_map(function (User $user) {
            return [
                'id' => $user->id,
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'email' => $user->email
            ];
        }, $query->all());
    }
}
