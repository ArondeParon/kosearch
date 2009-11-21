<?php defined('SYSPATH') or die('no direct scrip access');

/**
 * Represents a Model's search field, describing the type of Index
 *
 * filename:	Search_Field.php
 * version:		1.0
 *
 * @package		kosearch
 * @author		Howie Weiner (howie@microbubble.net)
 * @copyright	(c) 2009, Mirobubble Web Design
 * @license		http://opensource.org/licenses/mit-license.php MIT License
 * @version		1.0 (12th Nov 2009)
 * @link		http://github.com/microbubble/kosearch
 **/
class Search_Field {
	private $name;
	private $type;
	private $html_decode;

	/**
	 * @param String $name attribute name e.g. db table column name
	 * @param Zend_Search_Lucene_Field constant $type
	 * @param boolean $html_decode whether or not field data should be decoded prior to indexing- useful for CMS/WYSIWYG data. Default False
	 */
	function Search_Field($name, $type, $html_decode = FALSE) {
		$this->name = $name;
		$this->type = $type;
		$this->html_decode = $html_decode;
	}

	/**
	 * Accessor for private properties
	 */
	public function __get($var){
		return $this->$var;
	}
}