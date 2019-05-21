<?php
namespace Catalyst\Service;

use Catalyst\Model\YoYo\Resource\GM\GMExtension;
use Catalyst\Model\YoYo\Resource\GM\GMFolder;
use Catalyst\Model\YoYo\Resource\GM\GMFont;
use Catalyst\Model\YoYo\Resource\GM\GMIncludedFile;
use Catalyst\Model\YoYo\Resource\GM\GMNotes;
use Catalyst\Model\YoYo\Resource\GM\GMObject;
use Catalyst\Model\YoYo\Resource\GM\GMPath;
use Catalyst\Model\YoYo\Resource\GM\GMRoom;
use Catalyst\Model\YoYo\Resource\GM\GMScript;
use Catalyst\Model\YoYo\Resource\GM\GMShader;
use Catalyst\Model\YoYo\Resource\GM\GMSound;
use Catalyst\Model\YoYo\Resource\GM\GMSprite;
use Catalyst\Model\YoYo\Resource\GM\GMTileSet;
use Catalyst\Model\YoYo\Resource\GM\GMTimeline;
use Catalyst\Model\YoYo\Resource\GM\Options\GMAmazonFireOptions;
use Catalyst\Model\YoYo\Resource\GM\Options\GMAndroidOptions;
use Catalyst\Model\YoYo\Resource\GM\Options\GMHtml5Options;
use Catalyst\Model\YoYo\Resource\GM\Options\GMIosOptions;
use Catalyst\Model\YoYo\Resource\GM\Options\GMLinuxOptions;
use Catalyst\Model\YoYo\Resource\GM\Options\GMMacOptions;
use Catalyst\Model\YoYo\Resource\GM\Options\GMWindowsOptions;

class GMResourceService {

    const GM_OPTIONS = 'GMOptions';
    const GM_ROOT = 'root';

    const GM_FOLDER = 'GMFolder';

    const GM_ROOM = 'GMRoom';
    const GM_SPRITE = 'GMSprite';
    const GM_PATH = 'GMPath';
    const GM_TILESET = 'GMTileSet';
    const GM_SHADER = 'GMShader';
    const GM_OBJECT = 'GMObject';
    const GM_FONT = 'GMFont';
    const GM_TIMELINE = 'GMTimeline';
    const GM_SCRIPT = 'GMScript';
    const GM_SOUND = 'GMSound';

    const GM_INCLUDED_FILE = 'GMIncludedFile';
    const GM_NOTES = 'GMNotes';

    const GM_OPTIONS_HTML5 = 'GMHtml5Options';
    const GM_OPTIONS_IOS = 'GMiOSOptions';
    const GM_OPTIONS_AMAZON_FIRE = 'GMAmazonFireOptions';
    const GM_OPTIONS_LINUX = 'GMLinuxOptions';
    const GM_OPTIONS_WINDOWS = 'GMWindowsOptions';
    const GM_OPTIONS_ANDROID = 'GMAndroidOptions';
    const GM_OPTIONS_MAC = 'GMMacOptions';

    const GM_EXTENSION = 'GMExtension';

    const TYPEMAP = [
        self::GM_FOLDER => GMFolder::class,

        self::GM_ROOM => GMRoom::class,
        self::GM_SPRITE => GMSprite::class,
        self::GM_PATH => GMPath::class,
        self::GM_TILESET => GMTileSet::class,
        self::GM_SHADER => GMShader::class,
        self::GM_OBJECT => GMObject::class,
        self::GM_FONT => GMFont::class,
        self::GM_TIMELINE => GMTimeline::class,
        self::GM_SCRIPT => GMScript::class,
        self::GM_SOUND => GMSound::class,

        self::GM_INCLUDED_FILE => GMIncludedFile::class,
        self::GM_NOTES => GMNotes::class,

        self::GM_OPTIONS_HTML5 => GMHtml5Options::class,
        self::GM_OPTIONS_ANDROID => GMAndroidOptions::class,
        self::GM_OPTIONS_IOS => GMIosOptions::class,
        self::GM_OPTIONS_AMAZON_FIRE => GMAmazonFireOptions::class,
        self::GM_OPTIONS_LINUX => GMLinuxOptions::class,
        self::GM_OPTIONS_WINDOWS => GMWindowsOptions::class,
        self::GM_OPTIONS_MAC => GMMacOptions::class,

        self::GM_EXTENSION => GMExtension::class,
    ];

    private static $instance = null;

    private $mapping = [];

    private function __construct()
    {
    }

    public static function getInstance(): GMResourceService
    {
        if (self::$instance == null) {
            self::$instance = new GMResourceService();
        }

        return self::$instance;
    }

    private function loadPropertiesFromClass(string $className)
    {
        $this->mapping[$className] = [];

        $refClass = new \ReflectionClass($className);
        foreach ($refClass->getProperties() as $refProperty) {
            if (preg_match('/@var\s+([^\s]+)/', $refProperty->getDocComment(), $matches)) {
                list(, $type) = $matches;
                $this->mapping[$className][$refProperty->getName()] = $type;
            }
        }
    }

    private function _getPropertyForKey(string $key, string $className)
    {
        if (!array_key_exists($className, $this->mapping)) {
            $this->loadPropertiesFromClass($className);
        }

        if (array_key_exists($key, $this->mapping[$className])) {
            return $this->mapping[$className][$key];
        }

        return false;
    }

    public static function getPropertyForKey(string $key, string $className) {
        return self::getInstance()->_getPropertyForKey($key, $className);
    }
}