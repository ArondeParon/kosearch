<?php defined('SYSPATH') or die('No direct script access.');

class Cd_Model extends Track_Model
{
	public function __construct($id, $artist, $title)
	{
		parent::__construct($id, $artist, $title);
    }
}