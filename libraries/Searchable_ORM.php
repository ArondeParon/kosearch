<?php defined('SYSPATH') or die('no direct scrip access');

/**
 * Implementation of ORM interface for ORM Models
 *
 * filename:	Searchable_ORM.php
 * version:		1.0
 *
 * @package		kosearch
 * @author		Howie Weiner (howie@microbubble.net)
 * @copyright	(c) 2009, Mirobubble Web Design
 * @license		http://opensource.org/licenses/mit-license.php MIT License
 * @version		1.0 (12th Nov 2009)
 * @link		http://github.com/microbubble/kosearch
 **/
abstract class Searchable_ORM_Core extends ORM implements Searchable {

  	public function get_identifier()
	{
		return $this->__get($this->primary_key);
	}

	public function get_type()
	{
		return $this->object_name;
	}

	public function get_unique_identifier()
	{
		return $this->object_name."_". $this->get_identifier();
	}
}