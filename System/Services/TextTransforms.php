<?php

namespace System\Services;

/**
 * Toolkit for various text transformations
 */
class TextTransforms
{
	const TRANSFORM_PLURAL_TO_SINGLE = "pts";
	const TRANSFORM_SINGLE_TO_PLURAL = "stp";
	const TRANSFORM_SINGLE_LABEL = "single";
	const TRANSFORM_PLURAL_LABEL = "plural";

	/**
	 * Rules for pluralization transformations
	 * NOTE: ORDER BY DECLINING SIZE!
	 *
	 * @var array
	 */
	const RULES = array(
		array(
			self::TRANSFORM_PLURAL_LABEL => 'ies',
			self::TRANSFORM_SINGLE_LABEL => 'y'
		),
		array(
			self::TRANSFORM_PLURAL_LABEL => 's',
			self::TRANSFORM_SINGLE_LABEL => ''
		)
	);

	/**
	 * Change things like someAwesomeString to some_awsome_string
	 *
	 * @param  string $str The string to transform
	 * @return string      Snake case string representation
	 */
	public static function camelCaseToSnakeCase($str)
	{
		return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $str));
	}

	/**
	 * Change things like some_awsome_string to someAwesomeString
	 *
	 * @param  string $str The string to transform
	 * @return string      Camel case string representation
	 */
	public static function snakeCaseToCamelCase($str)
	{
		return str_replace('_', '', ucwords($str, '_'));
	}

	/**
	 * Translate plural names to single names
	 *
	 * @param  string $str String to be singlized
	 * @return string      Single version of the word
	 */
	public static function pluralToSingle($str)
	{
		return TextTransforms::aToBTransform($str, self::TRANSFORM_PLURAL_TO_SINGLE);
	}

	/**
	 * Translate single names to plural names
	 *
	 * @param  string $str String to be pluralized
	 * @return string      Plural version of the word
	 */
	public static function singleToPlural($str)
	{
		return TextTransforms::aToBTransform($str, self::TRANSFORM_SINGLE_TO_PLURAL);
	}

	/**
	 * Translate plural/single names to single/plural names
	 *
	 * @param  string $str String to be transformed
	 * @return string      Plural/Single version of the word
	 */
	public static function aToBTransform($str, $direction)
	{
		foreach(self::RULES as $rule) {
			$a = $b = "";
			switch($direction) {
				case self::TRANSFORM_PLURAL_TO_SINGLE:
					$a = $rule[self::TRANSFORM_PLURAL_LABEL];
					$b = $rule[self::TRANSFORM_SINGLE_LABEL];
					break;
				case self::TRANSFORM_SINGLE_TO_PLURAL:
					$a = $rule[self::TRANSFORM_SINGLE_LABEL];
					$b = $rule[self::TRANSFORM_PLURAL_LABEL];
					break;
			}
			if(substr($str, -strlen($a)) === $a) {
				return substr($str, 0, -strlen($a)).$b;
			}
			if(empty($a) && $rule === self::RULES[sizeof(self::RULES) - 1]) {
				return $str.$b;
			}
		}
		return $str;
	}
}
