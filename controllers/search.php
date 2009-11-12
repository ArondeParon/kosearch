<?php defined('SYSPATH') OR die('No direct access allowed.');

class Search_Controller extends Controller {

	const ALLOW_PRODUCTION = FALSE;

	public function index($msg = NULL) { 
		
		$results = NULL;
		$query = NULL;
		
		$view = new View('search');
	
		$view->bind("results", $results)
			->bind("query", $query)
			->bind("msg", $msg);
		
		if (!empty($_GET["q"])) {
			
			try {
				$query = $_GET["q"];
				
				$search = new Search;
				$results = $search->find($query);
			}
			catch(Exception $e) {
				Kohana::log("error", $e);
			}
		}
	
		$view->render(TRUE);
	}   
    
	public function add() {

		$items = array();
				
		$song = new Mp3_Model(1, "Ian Brown", "My Star");
		$items[] = $song;
		
		$song = new Mp3_Model(2, "Rolling Stones", "Brown Sugar");
		$items[] = $song;
		
		$song = new Cd_Model(3, "Stone Roses", "Sugar Spun Sister");
		$items[] = $song;
		
		$song = new Cd_Model(4, "David Bowie", "Starman");
		$items[] = $song;

		$song = new Mp3_Model(4, "Bob Dylan", "Like a Rolling Stone");
		$items[] = $song;
        
        
		try {
			$search = new Search;
			$search->build_search_index($items);
			
			$this->index("Index successfully populated");
		}
		catch(Exception $e) {
			$this->index($e);
		}
	}
		
} // End Search Controller