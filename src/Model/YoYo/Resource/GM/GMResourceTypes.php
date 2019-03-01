<?php
namespace GMDepMan\Model\YoYo\Resource\GM;

use GMDepMan\Model\YoYo\Resource\GM\Options\GMAmazonFireOptions;
use GMDepMan\Model\YoYo\Resource\GM\Options\GMAndroidOptions;
use GMDepMan\Model\YoYo\Resource\GM\Options\GMHtml5Options;
use GMDepMan\Model\YoYo\Resource\GM\Options\GMIosOptions;
use GMDepMan\Model\YoYo\Resource\GM\Options\GMLinuxOptions;
use GMDepMan\Model\YoYo\Resource\GM\Options\GMMacOptions;
use GMDepMan\Model\YoYo\Resource\GM\Options\GMWindowsOptions;

class GMResourceTypes {

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
    ];
}