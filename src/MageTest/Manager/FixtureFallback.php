<?php 

namespace MageTest\Manager;


/**
 * Class FixtureFallback
 *
 * @package MageTest\Manager
 */
final class FixtureFallback
{
    /**
     * Where project specific fixtures should be stored
     */
    const CUSTOM_DIRECTORY_LOCATION = 'tests/fixtures';

    /**
     * Where default fixtures are stored
     */
    const DEFAULT_DIRECTORY_LOCATION = '/Fixtures';

    /**
     * Fixture formats fallback order
     *
     * @var array
     */
    public static $sequence = [
        '.php',
        '.yml',
        '.json',
        '.xml'
    ];

    /**
     * Registry that matches resource names to fixture file names
     *
     * @var array
     */
    public static $fixtureTypes = [
        'sales/quote' => 'order',
        'admin/user' => 'admin'
    ];

    /**
     * Fixture location fallback order
     *
     * @return array
     */
    public static function locationSequence()
    {
        return [
            static::getCustomLocation(),
            static::getDefaultLocation()
        ];
    }

    /**
     * @return string
     */
    private static function getCustomLocation()
    {
        return getcwd() . DIRECTORY_SEPARATOR . static::CUSTOM_DIRECTORY_LOCATION;
    }

    /**
     * @return string
     */
    private static function getDefaultLocation()
    {
        return __DIR__ . static::DEFAULT_DIRECTORY_LOCATION;
    }

    /**
     * @param $fixtureType
     * @param $fileType
     * @return string
     */
    public static function getFileName($fixtureType, $fileType)
    {
        if (isset(static::$fixtureTypes[$fixtureType])) {
            return static::$fixtureTypes[$fixtureType] . $fileType;
        }
        return static::parseFileName($fixtureType) . $fileType;
    }

    /**
     * @param $fixtureType
     * @return mixed
     */
    private static function parseFileName($fixtureType)
    {
        $bits = explode('/', $fixtureType);
        return end($bits);
    }

}