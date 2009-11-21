<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Search service provoding access to underlying index
 *
 * filename:	Search.php
 * version:		1.0
 *
 * @package		kosearch
 * @author		Howie Weiner (howie@microbubble.net)
 * @copyright	(c) 2009, Mirobubble Web Design
 * @license		http://opensource.org/licenses/mit-license.php MIT License
 * @version		1.0 (12th Nov 2009)
 * @link		http://github.com/microbubble/kosearch
 **/
class Search_Core {

	const CREATE_NEW = TRUE;

	private static $instance = NULL;

	private $index_path, $index;

	/**
	 * singleton pattern
	 *
	 * @return instance of this class
	 **/
	public static function instance() {

		if(self::$instance == NULL) {
			self::$instance = new Search_Core();
		}

		return self::$instance;
	}

	/**
	 * Private constructor for singleton pattern
	 */
	private function __construct() {
		$this->index_path = Kohana::config('kosearch.index_path');

		if( !file_exists($this->get_index_path())) {
			throw new Kohana_User_Exception('Invalid index path', 'Could not find index path '.$this->get_index_path());
		}
		elseif(! is_dir($this->get_index_path())) {
			throw new Kohana_User_Exception('Invalid index path', 'index path id not a directory');
		}
		elseif(! is_writable($this->get_index_path())) {
			throw new Kohana_User_Exception('Invalid index path', 'Could not find index path ');
		}

		// set include path
		if ($path = Kohana::find_file('vendor', 'Zend/Loader'))
		{
		    ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.dirname(dirname($path)));
		}

		$this->load_search_libs();

		if( Kohana::config('kosearch.use_english_stemming_analyser') ){
			// use stemming analyser - http://codefury.net/2008/06/a-stemming-analyzer-for-zends-php-lucene/
			Zend_Search_Lucene_Analysis_Analyzer::setDefault(new StandardAnalyzer_Analyzer_Standard_English());
		}
		else {
			// set default analyzer to UTF8 with numbers, and case insensitive. Number are useful when searching on e.g. product codes
			Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive());
		}
	}

	/**
	 * Query the index
	 * @param	String $query	Lucene query
	 * @return	Zend_Search_Lucene_Search_QueryHit	hits
	 */
	public function find($query) {
		$this->open_index();
		return $this->index->find($query);
	}

	/**
	 * Add an entry
	 *
	 * @param Searchable	$item Model	implememting Searchable interface
	 * @param bool			$create_new	whether or not to create new index when adding item - only used when index is rebuilt
	 * @return Search		return this instance for method chaining
	 */
	public function add($item, $create_new = FALSE) {

		// ensure item implements Searchable interface
		if(! is_a($item, "Searchable")) {
			throw new Kohana_User_Exception('Invalid Object', 'Object must implement Searchable Interface');
		}

		if(!$create_new) {
			$this->open_index();
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

		// return this so we can have chainable methods
		return $this;
	}

	/**
	 * Update an entry
	 * We must first remove the entry from the index, then re-add it. To remove, we must find it by unique identifier
	 *
	 * @param Searchable $item	Model to update
	 * @return Search		return this instance for method chaining
	 */
	public function update($item) {

		$this->remove($item)->add($item);

		// return this so we can have chainable methods
		return $this;
	}

	/**
	 * Remove an entry from the index
	 *
	 * @param Searchable $item	Model to remove
	 * @return Search		return this instance for method chaining
	 */
	public function remove($item) {

		// now we have the identifier, find it
		$hits = $this->find('uid:'.$item->get_unique_identifier());

		if(sizeof($hits) == 0) {
			Kohana::log("error", "No index entry found for id ".$item->get_unique_identifier());
		}
		else if(sizeof($hits) > 1) {
			Kohana::log("error", "Non-unique Identifier - More than one record was returned");
		}

		if(sizeof($hits) > 0) {
			$this->open_index()->delete($hits[0]->id);
		}

		// return this so we can have chainable methods
		return $this;
	}

	/**
	 * Build new site index
	 * @param	Array	$items	array of Models to add
	 * @return	Search	return this instance for method chaining
	 */
	public function build_search_index($items) {
        // rebuild new index - create, not open
		$this->create_index();

		foreach($items as $item) {
			$this->add($item, self::CREATE_NEW);
		}

		$this->index->optimize();

		// return this so we can have chainable methods
		return $this;
	}

	/**
	 * Return underlying Lucene index to allow use of Zend API
	 * @return	Zend_Search_Lucene	the index
	 */
	public function get_index() {

		$this->create_index();

		return $this->index;
	}

	/**
	 * Load Zend classes. Requires calling externally if get_index() is used,
	 * or if Zend classes need instatiating
	 */
	public function load_search_libs() {

		require_once 'Zend/Loader/Autoloader.php';

		require_once 'StandardAnalyzer/Analyzer/Standard/English.php';

		Zend_Loader_Autoloader::getInstance();
	}

	/**
	 * Add a Zend document - utility call to underlying Zend method
	 *
	 * @param Zend_Search_Lucene_Document	$doc 	Document to add
	 * @return	Search	return this instance for method chaining	
	 */
	public function addDocument(Zend_Search_Lucene_Document $doc) {
		
		$this->open_index()->addDocument($doc);

		// return this so we can have chainable methods
		return $this;		
	}
	
	private function get_index_path() {
		return APPPATH.$this->index_path;
	}

	private function open_index() {

		if(empty($this->index)) {
			try {
				$this->index = $index = Zend_Search_Lucene::open($this->get_index_path()); // Open existing index;
			}
			catch(Zend_Search_Lucene_Exception $e) {
				$this->index = Zend_Search_Lucene::create($this->get_index_path()); // fall back to creating new one
			}
		}

		// return index for method chaining.
		return $this->index;
	}

	private function create_index() {

		if(empty($this->index)) {
			$this->index = Zend_Search_Lucene::create($this->get_index_path());
		}
	}

	/* prevent cloning of singleton */
	private function __clone() {}
}