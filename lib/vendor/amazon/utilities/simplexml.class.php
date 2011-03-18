<?php
/*
 * Copyright 2010-2011 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */


/*%******************************************************************************************%*/
// CLASS

/**
 * Wraps the underlying `SimpleXMLIterator` class with enhancements for rapidly traversing the DOM tree,
 * converting types, and comparisons.
 *
 * @version 2010.11.08
 * @license See the included NOTICE.md file for more information.
 * @copyright See the included NOTICE.md file for more information.
 * @link http://aws.amazon.com/php/ PHP Developer Center
 * @link http://php.net/SimpleXML SimpleXML
 */
class CFSimpleXML extends SimpleXMLIterator
{
	/**
	 * Stores the namespace name to use in XPath queries.
	 */
	public $xml_ns;

	/**
	 * Stores the namespace URI to use in XPath queries.
	 */
	public $xml_ns_url;

	/**
	 * Catches requests made to methods that don't exist. Specifically, looks for child nodes via XPath.
	 *
	 * @param string $name (Required) The name of the method.
	 * @param array $arguments (Required) The arguments passed to the method.
	 * @return mixed Either an array of matches, or a single <CFSimpleXML> element.
	 */
	public function __call($name, $arguments)
	{
		// Remap $this
		$self = $this;

		// Re-base the XML
		$self = new CFSimpleXML($self->asXML());

		// Determine XPath query
		$self->xpath_expression = 'descendant-or-self::' . $name;

		// Get the results and augment with CFArray
		$results = $self->xpath($self->xpath_expression);
		if (!count($results)) return false;
		$results = new CFArray($results);

		// If an integer was passed, return only that result
		if (isset($arguments[0]) && is_int($arguments[0]))
		{
			if (isset($results[$arguments[0]]))
			{
				return $results[$arguments[0]];
			}

			return false;
		}

		return $results;
	}

	/**
	 * Wraps the results of an XPath query in a <CFArray> object.
	 *
	 * @param string $expr (Required) The XPath expression to use to query the XML response.
	 * @return CFArray A <CFArray> object containing the results of the XPath query.
	 */
	public function query($expr)
	{
		return new CFArray($this->xpath($expr));
	}

	/**
	 * Gets the parent or a preferred ancestor of the current element.
	 *
	 * @param string $node (Optional) Name of the ancestor element to match and return.
	 * @return CFSimpleXML A <CFSimpleXML> object containing the requested node.
	 */
	public function parent($node = null)
	{
		if ($node)
		{
			$parents = $this->xpath('ancestor-or-self::' . $node);
		}
		else
		{
			$parents = $this->xpath('parent::*');
		}

		return $parents[0];
	}

	/**
	 * Gets the current XML node as a true string.
	 *
	 * @return string The current XML node as a true string.
	 */
	public function to_string()
	{
		return (string) $this;
	}

	/**
	 * Gets the current XML node as a true array.
	 *
	 * @return array The current XML node as a true array.
	 */
	public function to_array()
	{
		return new CFArray(json_decode(json_encode($this), true));
	}

	/**
	 * Gets the current XML node as a JSON string.
	 *
	 * @return string The current XML node as a JSON string.
	 */
	public function to_json()
	{
		return json_encode($this);
	}

	/**
	 * Whether or not the current node exactly matches the compared value.
	 *
	 * @param string $value (Required) The value to compare the current node to.
	 * @return boolean Whether or not the current node exactly matches the compared value.
	 */
	public function is($value)
	{
		return ((string) $this === $value);
	}

	/**
	 * Whether or not the current node contains the compared value.
	 *
	 * @param string $value (Required) The value to use to determine whether it is contained within the node.
	 * @return boolean Whether or not the current node contains the compared value.
	 */
	public function contains($value)
	{
		return (stripos((string) $this, $value) !== false);
	}
}
