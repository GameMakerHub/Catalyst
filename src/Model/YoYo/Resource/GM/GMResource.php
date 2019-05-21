<?php
namespace Catalyst\Model\YoYo\Resource\GM;

use Catalyst\Entity\CatalystEntity;
use Catalyst\Exception\FileNotFoundException;
use Catalyst\Model\Uuid;
use Catalyst\Model\YoYo\Resource;
use Catalyst\Service\GMResourceService;
use Catalyst\Service\StorageService;
use Catalyst\Traits\JsonUnpacker;

abstract class GMResource implements \JsonSerializable
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

    /** @var array */
    private $_children = [];

    /** @var bool */
    private $_edited = false;

    /** @var string */
    private $_filePath;

    /** @var Resource */
    private $_yypResource;

    public static function createFromObject(string $_filePath, \stdClass $object): GMResource
    {
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

    public function addChild(GMResource $child)
    {
        if (!isset($this->children)) {
            throw new \Exception('Cannot add child to resource that has no children property');
        }
        if (array_search((string) $child->id, $this->children) === false) {
            $this->children[] = (string) $child->id;
        }

        $this->_children[] = $child;
    }

    public function removeChild(string $id)
    {
        if (!isset($this->children)) {
            return;
        }

        foreach ($this->_children as $key => $child) {
            if ($child->id == $id) {
                echo 'Deleting ' . $id . ' from ' . $this->id . '('.$key.')' . PHP_EOL;

                foreach ($this->children as $key2 => $child2) {
                    if ($child2 == $id) {
                        unset($this->children[$key2]);
                    }
                }

                unset($this->_children[$key]);
                $this->markEdited();
            }
        }

    }

    public function addChildren(array $children)
    {
        $this->_children = $children;
    }

    public function getChildren()
    {
        return $this->_children;
    }

    public function isFolder():bool
    {
        return $this->modelName == GMResourceService::GM_FOLDER;
    }

    public function markEdited():void
    {
        $this->_edited = true;
    }

    public function isEdited():bool
    {
        return $this->_edited;
    }

    public function getFilePath()
    {
        return $this->_filePath;
    }

    public function jsonSerialize()
    {
        //Creating a new object to get the order of the JSON file OK, so the diff doesn't explode
        $newObj = new \stdClass();
        $newObj->id = $this->id;
        $newObj->modelName = $this->modelName;
        $newObj->mvc = $this->mvc;
        $newObj->name = $this->name;
        if (isset($this->children)) {
            $newObj->children = $this->children;
        }
        $newObj->filterType = $this->filterType;
        if (isset($this->folderName)) {
            $newObj->folderName = $this->folderName;
        }
        $newObj->isDefaultView = $this->isDefaultView;
        if (isset($this->localisedFolderName)) {
            $newObj->localisedFolderName = $this->localisedFolderName;
        }

        return $newObj;
    }

    public function getJson()
    {
        return str_replace("\n", "\r\n", json_encode($this, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function save()
    {
        //var_dump($this->getJson(), $this->getFilePath());
        if (!$GLOBALS['dry']) {
            file_put_contents($this->getFilePath(), $this->getJson());
        }
    }

    public function getYypResource()
    {
        return $this->_yypResource;
    }

    public function setYypResource(Resource $yypResource)
    {
        $this->_yypResource = $yypResource;
    }

    public function delete()
    {
        //echo 'TO RM:  ' . $this->getFilePath() . '('.dirname($this->getFilePath()).')';
        if ($this instanceof GMFolder) {
            unlink($this->getFilePath());
        } else {
            $this->rrmdir(dirname($this->getFilePath()));
        }

    }

    private function rrmdir($path) {
        // Open the source directory to read in files
        $i = new \DirectoryIterator($path);
        foreach($i as $f) {
            if($f->isFile()) {
                unlink($f->getRealPath());
            } else if(!$f->isDot() && $f->isDir()) {
                $this->rrmdir($f->getRealPath());
            }
        }
        rmdir($path);
    }
}