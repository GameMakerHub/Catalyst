<?php
namespace Catalyst\Model\YoYo\Resource\GM;

use Catalyst\Interfaces\SaveableEntityInterface;
use Catalyst\Model\Uuid;
use Catalyst\Model\YoYo\Resource\GM\Options\GMAmazonFireOptions;
use Catalyst\Model\YoYo\Resource\GM\Options\GMAndroidOptions;
use Catalyst\Model\YoYo\Resource\GM\Options\GMHtml5Options;
use Catalyst\Model\YoYo\Resource\GM\Options\GMIosOptions;
use Catalyst\Model\YoYo\Resource\GM\Options\GMLinuxOptions;
use Catalyst\Model\YoYo\Resource\GM\Options\GMMacOptions;
use Catalyst\Model\YoYo\Resource\GM\Options\GMMainOptions;
use Catalyst\Model\YoYo\Resource\GM\Options\GMTVOSOptions;
use Catalyst\Model\YoYo\Resource\GM\Options\GMWindowsOptions;
use Catalyst\Service\GMResourceService;
use Catalyst\Service\JsonService;
use Catalyst\Service\StorageService;
use Catalyst\Traits\JsonUnpacker;

abstract class GMResource implements SaveableEntityInterface
{
    use JsonUnpacker;

    /** @var \Catalyst\Model\Uuid */
    public $id;

    /** @var string */
    public $modelName;

    /** @var string */
    public $mvc = '1.1';

    /** @var string */
    public $name;

    /** @var bool */
    public $isDefaultView = false;

    /** @var string */
    public $localisedFolderName = '';

    /** @var string */
    public $filterType;

    /** @var string */
    public $folderName;

    /** @var bool */
    private $_ignored;

    /** @var string */
    private $_filePath;

    /** @var GMResource[] */
    private $_gmChildrenResources = [];

    /** @var string */
    private $_originalContents = '';

    public function getTypeName(): string
    {
        return substr(get_class($this), strrpos(get_class($this), '\GM', 0)+3, strlen(get_class($this)));
    }

    public static function createFromObject(string $_filePath, \stdClass $object, string $originalContents): GMResource
    {
        $keyValues = [];

        foreach ($object as $key => $data) {
            $propertyType = GMResourceService::getPropertyForKey($key, self::class);
            if (false === $propertyType) {
                //throw new \RuntimeException('Class ' . self::class . ' missing property ' . $key);
                $keyValues[$key] = $data; // Don't want to define every complete GM resource in PHP.. Unless we need it.
                continue;
            }

            if (substr($propertyType, -2, 2) == '[]') {
                // This is an array of data!
                $keyValues[$key] = [];

                /** @var GMResource $newClass */
                $newClass = substr($propertyType, 0, -2);
                foreach ($data as $newItem) {
                    die('TODO ' . __FILE__ . ':' . __LINE__);
                    $keyValues[$key][] = $newClass::createFromObject($newItem);
                }
                continue;
            } else {
                switch ($propertyType) {
                    case 'bool':
                        $keyValues[$key] = (bool) $data;
                        break;
                    case 'string':
                        $keyValues[$key] = (string) $data;
                        break;
                    case '\\' . Uuid::class:
                        $keyValues[$key] = Uuid::createFromString($data);
                        break;
                    default:
                        throw new \RuntimeException(
                            sprintf(
                                'Unknown property type %s for %s (%s)',
                                $propertyType,
                                self::class,
                                $key
                            )
                        );
                        break;
                }
            }
        }

        return new static(
            $_filePath,
            $keyValues,
            $originalContents
        );
    }

    public static function createFromFile(string $_filePath): GMResource
    {
        return self::createFromObject($_filePath, StorageService::getInstance()->getJson($_filePath), StorageService::getInstance()->getContents($_filePath));
    }

    private function __construct(string $_filePath, $keyValues, $originalContents)
    {
        $this->_filePath = $_filePath;
        foreach ($keyValues as $key => $value) {
            $this->{$key} = $value;
        }
        $this->_originalContents = $originalContents;
    }

    public function isOption(): bool
    {
        return (
            $this instanceof GMAmazonFireOptions
            || $this instanceof GMAndroidOptions
            || $this instanceof GMHtml5Options
            || $this instanceof GMIosOptions
            || $this instanceof GMLinuxOptions
            || $this instanceof GMMacOptions
            || $this instanceof GMMainOptions
            || $this instanceof GMTVOSOptions
            || $this instanceof GMWindowsOptions
        );
    }

    public function isIncludedFile(): bool
    {
        return ($this instanceof GMIncludedFile);
    }

    public function getName(): string
    {
        if ($this->isFolder()) {
            return $this->folderName;
        }

        if ($this->localisedFolderName != '') {
            return $this->localisedFolderName;
        }
        
        return $this->name;
    }

    public function addNewChildResource(GMResource $GMResource)
    {
        if (!$this->isFolder()) {
            throw new \Exception('Can not add a resource to a resource - only folder! ('.$GMResource->name.')');
        }

        $this->children[] = (string) $GMResource->id;
        $this->linkChildResource($GMResource);
        $this->forceRegenerationOnSave(); //Reset so it gets overwritten / regenerated
        StorageService::getInstance()->writeFile($this->_filePath, $this->getJson());
    }

    public function linkChildResource(GMResource $GMResource)
    {
        $this->_gmChildrenResources[] = $GMResource;
    }

    public function removeChildResourceByUuid(Uuid $uuid)
    {
        $this->_gmChildrenResources = array_filter(
            $this->_gmChildrenResources,
            function($resource) use ($uuid) {
                if ($resource->isOption()) { return true; }
                return (!$uuid->equals(Uuid::createFromString($resource->id)));
            }
        );

        if (($key = array_search((string) $uuid, $this->children)) !== false) {
            unset($this->children[$key]);
        }
    }

    /**
     * @return GMResource[]
     */
    public function getChildResources(): array
    {
        return $this->_gmChildrenResources;
    }

    /**
     * @param $name
     * @return false|GMResource
     */
    public function findChildResourceByName($name)
    {
        foreach ($this->getChildResources() as $resource) {
            if ($resource->isFolder()) {
                if ($resource->folderName == $name) {
                    return $resource;
                }
            } else {
                if ($resource->name == $name) {
                    return $resource;
                }
            }
        }
        return false;
    }

    public function isFolder(): bool
    {
        return $this->modelName == GMResourceService::GM_FOLDER;
    }

    public function getFilePath(): string
    {
        return $this->_filePath;
    }

    public function forceRegenerationOnSave()
    {
        $this->_originalContents = '';
    }

    public function getJson()
    {
        if ($this->_originalContents != '') {
            return $this->_originalContents;
        }

        // Custom object for view files to prevent a load of changes to files
        if ($this->isFolder()) {
            $newObject = new \stdClass();
            $newObject->id = (string) $this->id;
            $newObject->modelName = $this->modelName;
            $newObject->mvc = $this->mvc;
            $newObject->name = $this->name;
            $newObject->children = array_values(array_unique($this->children));
            $newObject->filterType = $this->filterType;
            $newObject->folderName = $this->folderName;
            $newObject->isDefaultView = $this->isDefaultView;
            $newObject->localisedFolderName = $this->localisedFolderName;

            return JsonService::encode($newObject);
        }

        //Fallback for generation
        $newObject = new \stdClass();
        foreach (get_object_vars($this) as $key => $value) {
            if (substr($key, 0, 1) != '_') {
                if (is_object($value)) {
                    try {
                        $value = $value->__toString();
                    } catch (\Throwable $e) {
                        echo 'Could not convert to string;';
                        var_dump($value);
                        throw new \Exception('Ded.');
                    }

                }
                $newObject->$key = $value;
            }
        }

        return JsonService::encode($newObject);
    }

    public function getFileContents(): string
    {
        return $this->getJson();
    }
}