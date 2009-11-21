<?php defined('SYSPATH') OR die('No direct access allowed.');

class Search_Example_Controller extends Controller {

	const ALLOW_PRODUCTION = FALSE;

	public function index($msg = NULL) {

		$results = NULL;
		$results2 = NULL;
		$query = NULL;

		$view = new View('search_example');

		$view->bind("results", $results)
			->bind("results2", $results2)
			->bind("query", $query)
			->bind("msg", $msg);

		if (!empty($_GET["q"])) {

			try {
				$query = $_GET["q"];
				$form = $_GET["form"];

				if($form == "artists") {
					$results = Search::instance()->find($query);
				}
				else {
					Search::instance()->load_search_libs();

					$query = Zend_Search_Lucene_Search_QueryParser::parse($query);

					$hits = Search::instance()->find($query);

					$results2 = $query->highlightMatches(iconv('UTF-8', 'ASCII//TRANSLIT', $hits[0]->body));
				}
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
			Search::instance()->build_search_index($items);

			$this->index("Index successfully populated");
		}
		catch(Exception $e) {
			$this->index($e);
		}
	}

	public function addurl() {

		// use a local file for purpose of demo.
		$filename = MODPATH."kosearch".DIRECTORY_SEPARATOR."examples".DIRECTORY_SEPARATOR."kohana_home.html";

		// Note: the Search class is responsible for loading the Zend libraries, so as we
		// want to instantiate Zend_Search_Lucene_Document_Html prior to calling singleton,
		// we must first call Search::instance()->load_search_libs();
		Search::instance()->load_search_libs();

		$doc = Zend_Search_Lucene_Document_Html::loadHTMLFile($filename, TRUE, "utf-8");

		Search::instance()->addDocument($doc);

		$this->index('Kohana page successfully added &darr;&nbsp;<a href="#form2" title="scroll down">scroll down</a>&nbsp;&darr;');
	}

} // End Search Controller