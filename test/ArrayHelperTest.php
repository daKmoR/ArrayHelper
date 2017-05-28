<?php

	require_once (__DIR__ . '/../ArrayHelper.php');

	class ArrayHelperTest extends PHPUnit_Framework_TestCase {

		public function testWalkRecursiveWithSimpleRemapping() {
			$input = array(
				'a' => 'root-a-value',
				'b' => 'root-b-value',
				'some' => array('a' => 'sub-a-value', 'b' => 'sub-b-value'),
				'more' => 5,
				'again'
			);

			$expected = array(
				'a' => 'new-a-value',
				'b' => 'root-b-value',
				'some' => array('a' => 'new-a-value', 'b' => 'overwrite-only-sub-b'),
				'extra-more' => 10,
				0 => 'again'
			);

			$result = ArrayHelper::walkRecursive($input, function ($key, $value, $path) {
				if ($key === 'more') {
					$key = 'extra-more';
					$value = $value*2;
				}
				if ($key === 'a') {
					$value = 'new-a-value';
				}
				if ($path . '.' . $key === 'some.b') {
					$value = 'overwrite-only-sub-b';
				}
				return array('key' => $key, 'value' => $value);
			});

			$this->assertEquals($expected, $result);
		}

	}