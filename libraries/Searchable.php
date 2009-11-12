<?php defined('SYSPATH') or die('no direct scrip access');

/**
 * Searchable Interface for use by Search Class.
 */
interface Searchable {
	
	const KEYWORD = 0;
	const UNINDEXED = 1;
	const BINARY = 2;
	const TEXT = 3;
	const UNSTORED = 4;

	const DECODE_HTML = TRUE;
	const DONT_DECODE_HTML = FALSE;
	
	/**
	 * @return array of Search_Field objects
	 */
	function get_indexable_fields();	
	
	/**
	 * @return mixed identifier for this item
	 * For ORM Models this would be the PK
	 */
	function get_identifier();	
	
	 /**
	 * @return String type of item
	 * For ORM Models this would likely be the object name
	 */
	function get_type();	
	
	/**
	 * @return mixed unique id of this item
	 */
	function get_unique_identifier();
}