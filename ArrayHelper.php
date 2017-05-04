<?php

/**
 * Array Helper
 *
 **/
Class ArrayHelper {

	/**
	 * Example:
	 *   // array(0 => 'a', 1 => 'b');
	 *   ArrayHelper::addAtBeginning($input, 'someValue');
	 *   // => array(0 => 'someValue', 1 => 'a', 2 => 'b');
	 *
	 *   // => array('a' => 'aa', 'b' => 'bb');
	 *   ArrayHelper::addAtBeginning($input, 'someKey', 'someValue);
	 *   // => array('someKey' => 'someValue', 'a' => 'aa', 'b' => 'bb');
	 *
	 * @param array $array
	 * @param       $key
	 * @param null  $value
	 * @return array|int
	 */
	public static function addAtBeginning($array, $key, $value = null) {
		if (isset($value)) {
			$array = array_reverse($array, true);
			$array[$key] = $value;
			$array = array_reverse($array, true);
			return $array;
		} else {
			array_unshift($array, $key);
			return $array;
		}
	}

	/**
	 * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
	 * keys to arrays rather than overwriting the value in the first array with the duplicate
	 * value in the second array, as array_merge does. I.e., with array_merge_recursive,
	 * this happens (documented behavior):
	 *
	 * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
	 *     => array('key' => array('org value', 'new value'));
	 *
	 * Parameters are passed by reference, though only for performance reasons. They're not
	 * altered by this function.
	 *
	 * @param array $array1
	 * @param mixed $array2
	 * @author daniel@danielsmedegaardbuus.dk
	 * @return array
	 */
	public static function mergeRecursiveOverwrite(array $array1, $array2 = null) {
		$merged = $array1;
		if (is_array($array2)) {
			foreach ($array2 as $key => $val) {
				if (is_array($array2[$key]) && isset($merged[$key])) {
					$merged[$key] = is_array($merged[$key]) ? static::mergeRecursiveOverwrite($merged[$key], $array2[$key]) : $array2[$key];
				} else {
					$merged[$key] = $val;
				}
			}
		}
		return $merged;
	}

	public static function mergeOnlyUndefined($array1, $array2) {
		$merged = $array1;
		if (is_array($array2)) {
			foreach ($array2 as $key => $val) {
				if (is_array($array2[$key]) && isset($merged[$key])) {
					$merged[$key] = is_array($merged[$key]) ? static::mergeOnlyUndefined($merged[$key], $array2[$key]) : $array2[$key];
				} else {
					if (!isset($merged[$key])) {
						$merged[$key] = $val;
					}
				}
			}
		}
		return $merged;
	}

	/**
	 * Merges any given number of arrays together, wheres the later arrays overwrite the values in earlier arrays.
	 * Works recursively.
	 *
	 * Example:
	 *   ArrayHelper::merge(
	 *     array('aa' => '1', 'ab' => '2'),
	 *     array('aa' => '9', 'ac' => '3'),
	 *     array('aa' => '5'),
	 *   );
	 *   ==> array('aa' => '5', 'ab' => '2', 'ac' => '3')
	 *
	 * @param $array1
	 * @param $array2
	 * @param ...
	 * @return array
	 */
	public static function merge($array1, $array2) {
		$arguments = func_get_args();
		$result =  $array1;
		foreach($arguments as $argument) {
			$result = static::mergeRecursiveOverwrite($result, $argument);
		}
		return $result;
	}

	/**
	 * Checks if the given key is the last key in an array.
	 *
	 * <code name="example">
	 *   <?php foreach($entries as $key => $entry) { ?>
	 *     <li <?php echo ArrayHelper::isLastKey($entries, $key) ? 'class="last"' : ''; ?>>
	 *       <?php echo $entry['name']; ?>
	 *     </li>
	 *   <?php } ?>
	 * </code>
	 *
	 * <code name="result">
	 *   // array has 2 elements and Name2 is the last one
	 *   <li>Name1</li>
	 *   <li class="last">Name2</li>
	 * </code>
	 *
	 * @param $array
	 * @param $key
	 * @return bool
	 */
	public static function isLastKey(&$array, $key) {
		end($array);
		return $key === key($array);
	}

	/**
	 * Convert stdClass Objects to Multidimensional Arrays
	 * Example:
	 *   $object = new stdClass();
	 *   $object->a = 'test me'
	 *   $array = ArrayHelper::arrayToObject($object);
	 *   echo $array['a']; //test me
	 *
	 * @link http://www.if-not-true-then-false.com/2009/php-tip-convert-stdclass-object-to-multidimensional-array-and-convert-multidimensional-array-to-stdclass-object/
	 * @param $object
	 * @return array
	 */
	public static function objectToArray($object) {
		if (is_object($object)) {
			// Gets the properties of the given object with get_object_vars function
			$object = get_object_vars($object);
		}

		if (is_array($object)) {
			return array_map(array(new static(), __FUNCTION__), $object);
		} else {
			return $object;
		}
	}

	/**
	 * Convert Multidimensional Arrays to stdClass Objects
	 * Example:
	 *   $object = ArrayHelper::arrayToObject(array('a' => 'test me'));
	 *   echo $object->a; //test me
	 *
	 * @link http://www.if-not-true-then-false.com/2009/php-tip-convert-stdclass-object-to-multidimensional-array-and-convert-multidimensional-array-to-stdclass-object/
	 * @param $array
	 * @return object
	 */
	public static function arrayToObject($array) {
		if (is_array($array)) {
			return (object) array_map(array(new static(), __FUNCTION__), $array);
		} else {
			return $array;
		}
	}

	/**
	 * Converts an array with 'key' and 'value' to associative array.
	 *
	 * Example:
	 *   ArrayHelper::valueKeyToKey(array('key' => 'a', 'valueA' => 'a', 'valueB' => 'b'));
	 *   => array('a' => array('valueA' => 'a', 'valueB' => 'b'));
	 *
	 *   ArrayHelper::valueKeyToKey(array(
	 *     array('key' => 'a', 'value' => 'av'),
	 *     array('key' => 'b', 'value' => 'bv'),
	 *     array('key' => 'a', 'value' => 'cv')
	 *   ));
	 *   => array('a' => 'cv', 'b' => 'bv');
	 *
	 * @param $inputArray
	 * @return array
	 */
	public static function valueKeyToKey($inputArray) {
		$outputArray = array();
		if (Is::notEmptyArray($inputArray) && isset($inputArray['key'])) {
			$inputArray = array($inputArray);
		}
		foreach($inputArray as $key => $inputArrayItem) {
			if (Is::notEmptyArray($inputArrayItem) && isset($inputArrayItem['key'])) {
				$key = $inputArrayItem['key'];
				unset($inputArrayItem['key']);
				if (count($inputArrayItem) > 1) {
					$outputArray[$key] = $inputArrayItem;
				} else {
					$outputArray[$key] = reset($inputArrayItem);
				}
			} else {
				$outputArray[$key] = $inputArrayItem;
			}
		}
		return $outputArray;
	}

	/**
	 * Walk an array recursively with ability to alter value AND key!
	 *
	 * Example:
	 *   $source = array(
	 *     'a' => 'root-a-value',
	 *     'b' => 'root-b-value',
	 *     'some' => array('a' => 'sub-a-value', 'b' => 'sub-b-value'),
	 *     'more' => 5
	 *   );
	 *   $array = ArrayHelper::walkRecursive($source,	function ($key, $value, $path) {
	 *     if ($key === 'more') {
	 *       $key = 'extra-more';
	 *       $value = $value*2;
	 *     }
	 *     if ($key === 'a') {
	 *       $value = 'new-a-value';
	 *     }
	 *     if ($path . '.' . $key === 'some.b') {
	 *       $value = 'overwrite-only-sub-b';
	 *     }
	 *     return array('key' => $key, 'value' => $value);
	 *   });
	 *
	 * Result:
	 *   $array = array(
	 *     'a' => 'new-a-value',
	 *     'b' => 'root-b-value',
	 *     'some' => array('a' => 'new-a-value', 'b' => 'overwrite-only-sub-b'),
	 *     'extra-more' => 10
	 *   );
	 *
	 * @param  array $input
	 * @param        $callback
	 * @param        $path
	 * @return array
	 */
	public static function walkRecursive($input, $callback, $path = '') {
		$newArray = array();
		foreach ($input as $key => $value) {
			if (is_array($value)) {
				$currentPath = $path ? $path . '.' . $key : $key;
				$value = static::walkRecursive($value, $callback, $currentPath);
			}
			$save = $callback($key, $value, $path);
			$newArray[$save['key']] = $save['value'];
		}
		return $newArray;
	}


	/**
	 * Filters arrays recursively
	 *
	 * Example:
	 *   ArrayHelper::filterRecursive(array('a' => array('b' => '', 'c' => false, 'd' => 'something));
	 *   => array('a' => array('d' => something'))
	 *
	 *   ArrayHelper::filterRecursive(array('a' => array('b' => '', 'c' => false, 'd' => 'something), function($value) {
	 *     return $value !== 'something';
	 *   });
	 *   => array('a' => array('b' => '', 'c' => false));
	 *
	 * @param      $input
	 * @param null $callback
	 * @return array
	 */
	public static function filterRecursive($input, $callback = null) {
		foreach ($input as &$value) {
			if (is_array($value)) {
				$value = static::filterRecursive($value, $callback);
			}
		}
		return array_filter($input, $callback);
	}

	/**
	 * Removes all 'null' (as String) values from an array. Works Recursively.
	 *
	 * Example:
	 *   ArrayHelper::cleanNullStrings(array('a' => 'aa', 'b' => 'null'));
	 *   => array('a' => 'aa')
	 *
	 * @param $inputArray
	 * @return array
	 */
	public static function cleanNullStrings($inputArray) {
		if (is_array($inputArray)) {
			return static::filterRecursive($inputArray, function($value) {
				return $value !== 'null';
			});
		}
		return is_string($inputArray) && $inputArray !== 'null' ? $inputArray : array();
	}

	/**
	 * Checks if at least ONE key exists in array
	 *
	 * @param array $keys
	 * @param array $array
	 * @return bool
	 */
	public static function keysExist($keys, $array) {
		if (is_array($array)) {
			foreach($keys as $key) {
				return array_key_exists($key, $array);
			}
		}
		return false;
	}

}