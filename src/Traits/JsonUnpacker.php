<?php
namespace Catalyst\Traits;

trait JsonUnpacker {

    public function unpack($originalData)
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
                        $newValue->unpack($newItem);
                        $this->{$key}[] = $newValue;
                    }
                    continue;
                } else {
                    $value = $data;
                    if (!in_array($propertyType, ['bool', 'string'])) {
                        /** @var JsonUnpacker $value */
                        $value = new $propertyType();
                        $value->unpack($data);
                    }

                    $this->{$key} = $value;
                    continue;
                }
            }

            // Not caught, so just appending to this object
            $this->{$key} = $data;
        }
    }

}