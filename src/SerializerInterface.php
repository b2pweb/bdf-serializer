<?php

namespace Bdf\Serializer;

/**
 * SerializerInterface
 */
interface SerializerInterface
{
    /**
     * Serializes the given data to the specified output format.
     *
     * @param object|array|mixed  $data
     * @param string              $format
     * @param array               $context
     *
     * @return string
     */
    public function serialize($data, $format, array $context = []);

    /**
     * Deserializes the given data to the specified type.
     *
     * @param mixed                $data
     * @param string|object        $type  The type of data. Can be the target object.
     * @param string               $format
     * @param array                $context
     *
     * @return object|array|mixed
     */
    public function deserialize($data, $type, $format, array $context = []);

    /**
     * Serialize data to json
     *
     * @param mixed  $data
     * @param array  $context
     *
     * @return string
     */
    public function toJson($data, array $context = []);

    /**
     * Restores objects from json.
     *
     * @param string         $json
     * @param string|object  $type  The type of data. Can be the target object.
     * @param array          $context
     *
     * @return mixed this returns whatever the passed type is, typically an object or an array of objects
     */
    public function fromJson(string $json, $type, array $context = []);

    /**
     * Converts objects to an array structure.
     *
     * This is useful when the data needs to be passed on to other methods which expect array data.
     *
     * @param mixed $data anything that converts to an array, typically an object or an array of objects
     * @param array $context
     *
     * @return array
     */
    public function toArray($data, array $context = []);
    
    /**
     * Restores objects from an array structure.
     *
     * @param array          $data
     * @param string|object  $type  The type of data. Can be the target object.
     * @param array          $context
     *
     * @return mixed this returns whatever the passed type is, typically an object or an array of objects
     */
    public function fromArray(array $data, $type, array $context = []);
}
