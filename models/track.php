<?php defined('SYSPATH') or die('No direct script access.');

class Track_Model implements Searchable 
{
	private $vars = array();
	
	public function __construct($id, $artist, $title)
	{
		$thid->id = $id;
		$this->artist = $artist;
		$this->title = $title;

		$this->object_name = strtolower(substr(get_class($this), 0, -6));
	}
	
	public function __get($var)
	{
		return isset($this->vars[$var]) ? $this->vars[$var] : null;
    }

	public function __set($name, $value)
	{
		$this->vars[$name] = $value;
	}
	
	/**
	 * Searchable interface implementation
	 */
	public function get_indexable_fields()
	{
		$fields = array();
		$fields[] = new Search_Field('artist', Searchable::TEXT);
		$fields[] = new Search_Field('title', Searchable::TEXT);
		return $fields;
	}
	
  	public function get_identifier()
	{
		return $this->__get($this->id);
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