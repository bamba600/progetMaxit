<?php
namespace App\Core\Abstract;

abstract class AbstractEntity
{
    abstract static function toObject(array $data): object;

    abstract static function toArray(object $object): array;
    
    public static function toJson(object $object): string
    {
        return json_encode(self::toArray($object));
    }
}
?>