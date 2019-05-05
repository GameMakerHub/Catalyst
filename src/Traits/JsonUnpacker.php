<?php
namespace Catalyst\Traits;

use Catalyst\Entity\CatalystEntity;

trait JsonUnpacker {

    public function unpack($originalData, CatalystEntity $depmanEntity)
    {
        foreach ($originalData as $key => $data) {

            $propertyType = $this->getPropertyForKey($key);
            if (false !== $propertyType) {
                if (substr($propertyType, -2, 2) == '[]') {

                    $this->{$key} = [];

                    $newClass = substr($propertyType, 0, -2);
                    foreach ($data as $newItem) {
                        /** @var JsonUnpacker $newValue */
                        $newValue = new $newClass();
                        $newValue->unpack($newItem, $depmanEntity);
                        $this->{$key}[] = $newValue;
                    }
                    continue;
                } else {
                    $value = $data;
                    if (!in_array($propertyType, ['bool', 'string'])) {
                        /** @var JsonUnpacker $value */
                        $value = new $propertyType();
                        $value->unpack($data, $depmanEntity);
                    }

                    $this->{$key} = $value;
                    continue;
                }
            }

            // Not caught, so just appending to this object
            $this->{$key} = $data;
        }
    }

    /**
     * @todo cache results
     * @param $key
     * @return mixed
     * @throws \ReflectionException
     */
    private function getPropertyForKey($key) {
        $refClass = new \ReflectionClass(self::class);
        foreach ($refClass->getProperties() as $refProperty) {
            if (preg_match('/@var\s+([^\s]+)/', $refProperty->getDocComment(), $matches)) {
                list(, $type) = $matches;
                if ($key == $refProperty->getName()) {
                    return $type;
                }
            }
        }
        return false;
    }
}