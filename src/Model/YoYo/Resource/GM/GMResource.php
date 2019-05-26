<?php
namespace Catalyst\Model\YoYo\Resource\GM;

use Catalyst\Model\Uuid;
use Catalyst\Service\GMResourceService;
use Catalyst\Service\StorageService;
use Catalyst\Traits\JsonUnpacker;

abstract class GMResource
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

    /** @var string */
    private $_filePath;

    /** @var GMResource[] */
    private $_gmChildrenResources = [];

    public static function createFromObject(string $_filePath, \stdClass $object): GMResource
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
            $keyValues
        );
    }

    public static function createFromFile(string $_filePath): GMResource
    {
        return self::createFromObject($_filePath, StorageService::getInstance()->getJson($_filePath));
    }

    private function __construct(string $_filePath, $keyValues)
    {
        $this->_filePath = $_filePath;
        foreach ($keyValues as $key => $value) {
            $this->{$key} = $value;
        }
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

    public function linkChildResource(GMResource $GMResource)
    {
        $this->_gmChildrenResources[] = $GMResource;
    }

    /**
     * @return GMResource[]
     */
    public function getChildResources(): array
    {
        return $this->_gmChildrenResources;
    }

    public function isFolder(): bool
    {
        return $this->modelName == GMResourceService::GM_FOLDER;
    }

    public function getFilePath(): string
    {
        return $this->_filePath;
    }

}