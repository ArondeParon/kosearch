<?php defined('SYSPATH') or die('No direct script access.');

class Search_Core {

	const CREATE_NEW = TRUE;
	
	private $index_path, $index;
	
	public function __construct() {
		$this->index_path = Kohana::config('search.index_path');
		
		if( !file_exists($this->get_index_path())) {
			throw new Kohana_User_Exception('Invalid index path', 'Could not find index path '.$this->get_index_path());
		}
		elseif(! is_dir($this->get_index_path())) {
			throw new Kohana_User_Exception('Invalid index path', 'index path id not a directory');			
		}
		elseif(! is_writable($this->get_index_path())) {
			throw new Kohana_User_Exception('Invalid index path', 'Could not find index path ');			
		}		
		
		$this->load_search_libs();
		                                                                                                        
		// set default analyzer to UTF8 with numbers, and case insensitive. Number are useful when searching on e.g. product codes
		//Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive());
		
		// use stemming analyser - http://codefury.net/2008/06/a-stemming-analyzer-for-zends-php-lucene/
		Zend_Search_Lucene_Analysis_Analyzer::setDefault(new StandardAnalyzer_Analyzer_Standard_English());
	}
		
	/**
	 * Query the index
	 * @param String $query Lucene query
	 * @return Zend_Search_Lucene_Search_QueryHit hits
	 */
	public function find($query) {
		$this->open_index();
		return $this->index->find($query);
	}

	/**
	 * Add an entry
	 */
	public function add($item, $create_new = FALSE) {
		
		if(!$create_new) {
			$this->open_index();
		}
		
		// ensure item implements Searchable interface
		if(! is_a($item, "Searchable")) {
			throw new Kohana_User_Exception('Invalid Object', 'Object must implement Searchable Interface');
		}

		$doc = new Zend_Search_Lucene_Document();

		// get indexable fields;
		$fields = $item->get_indexable_fields();

		// index the object type - this allows search results to be grouped/searched by type
		$doc->addField(Zend_Search_Lucene_Field::Keyword('type', $item->get_type()));

		// index the object's id - to avoid any confusion, we call it 'identifier' as Lucene uses 'id' attribute internally.
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('identifier', $item->get_identifier())); // store, but don't index or tokenize

		// index the object type plus identifier - this gives us a unique identifier for later retrieval - e.g. to delete
		$doc->addField(Zend_Search_Lucene_Field::Keyword('uid', $item->get_unique_identifier()));

		// index all fields that have been identified by Interface
		foreach($fields as $field) {
			// get attribute value from model
			$value = $item->__get($field->name);

			// html decode value if required
			$value = $field->html_decode ? htmlspecialchars_decode($value) : $value;

			// add field value based on type
			switch($field->type) {
				case Searchable::KEYWORD :
					$doc->addField(Zend_Search_Lucene_Field::Keyword($field->name, $value));
					break;

				case Searchable::UNINDEXED :
					$doc->addField(Zend_Search_Lucene_Field::UnIndexed($field->name, $value));
					break;

				case Searchable::BINARY :
					$doc->addField(Zend_Search_Lucene_Field::Binary($field->name, $value));
					break;

				case Searchable::TEXT :
					$doc->addField(Zend_Search_Lucene_Field::Text($field->name, $value));
					break;

				case Searchable::UNSTORED :
					$doc->addField(Zend_Search_Lucene_Field::UnStored($field->name, $value));
					break;
			}
		}
		$this->index->addDocument($doc);
	}

	/**
	 * Update an entry
	 * We must first remove the entry from the index, then re-add it. To remove, we must find it by unique identifier
	 */
	public function update($item) {

		$this->remove($item)->add($item);
	}

	/**
	 * Remove an entry from the index
	 */
	public function remove($item) {

		$this->open_index();		
		
		// now we have the identifier, find it
		$hits = $this->find('uid:'.$item->get_unique_identifier());

		if(sizeof($hits) == 0) {
			Kohana::log("error", "No index entry found for id ".$item->get_unique_identifier());
		}		
		else if(sizeof($hits) > 1) {
			Kohana::log("error", "Non-unique Identifier - More than one record was returned");
		}

		if(sizeof($hits) > 0) {
			$this->index->delete($hits[0]->id);
		}
		
		// return this so we can have chainable methods - for an update
		return $this;		
	}
	
	/**
	 * Build new site index
	 */
	public function build_search_index($items) {
        // rebuild new index - create, not open
		$this->create_index();

		foreach($items as $item) {
			$this->add($item, self::CREATE_NEW);
		}
		
		$this->index->optimize();
	}
	
	private function load_search_libs() {

		if ($path = Kohana::find_file('vendor', 'Zend/Loader'))
		{
		    ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.dirname(dirname($path)));
		}

		require_once 'Zend/Loader/Autoloader.php';
		
		require_once 'StandardAnalyzer/Analyzer/Standard/English.php';
		
		Zend_Loader_Autoloader::getInstance();
	}
	
	private function get_index_path() {
		return APPPATH.$this->index_path;
	}
	
	private function open_index() {
		
		if(empty($this->index)) {
			$this->index = $index = Zend_Search_Lucene::open($this->get_index_path()); // Open existing index;			
		}
	}

	private function create_index() {
		
		if(empty($this->index)) {
			$this->index = Zend_Search_Lucene::create($this->get_index_path(), true);	
		}
	}
	
	
}