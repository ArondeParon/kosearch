<?php defined('SYSPATH') or die('no direct scrip access');

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