<?php

function array_map_with_keys(callable $callable, array ...$arrays): array
{
    $mappedArray = [];

    foreach ($arrays as $array) {
        foreach ($array as $key => $value) {
            $result = $callable($value, $key);

            $mappedArray = array_replace($mappedArray, $result);
        }
    }

    return $mappedArray;
}
