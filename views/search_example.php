<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>Search Example</title>
	<style type="text/css" media="screen">
		body {font-family:Arial; color:#555;}
		a{color:#69c;}
		td{border-bottom:1px solid #ccc; padding:3px 5px;}
		th{border-bottom:2px solid #ccc; padding:3px 5px;; text-align:left;}
		table{margin-bottom:20px; width:600px;}
		hr {border-top:1px solid #ccc; border-bottom:none; border-left:none; border-right:none; height:1px;}
		.wrapper {width:960px; margin:2em auto;}
		li { padding:5px 0;}
		h1 {font-family:Baskerville,"Times New Roman",Georgia,sans-serif; color:#369;font-size: 3.6em;font-weight: normal;}
		h3 {margin-top:2em;}
		.graph {width:100px; height:10px; border:1px solid #aaa; background:#ccc;}
		.bar{background:#336699; height:10px;}
		label{font-weight:bold;}
	</style>
</head>
<body>
	<div class="wrapper">
		<h1>kosearch</h1>
		<h3>Introduction to kosearch</h3>
		<p>So, what is <em>kosearch?</em> It's a Search module for <a href="http://www.kohanaphp.com/" title="Kohana PHP">Kohana PHP</a>. 
			More specifically, it's an implementation of <a href="http://framework.zend.com/manual/en/zend.search.lucene.html" title="Zend (Lucene) Search">Zend (Lucene) Search</a>, 
			a file-based search/index solution. <em>kosearch</em> provides a simple way to index and search Models. 
			It's perfect for a web site that might contain news, products etc. <em>kosearch</em> also exposes the underlying Zend libraries so that other things 
			can be indexed - PDFs, web pages, Word docs etc.</p>
			<p>The <em>kosearch</em> module has been written for, and tested against Kohana 2.3.4</p>
		<h3>Why use Zend Lucene Search</h3>

		<p><strong>Q</strong>. Why Use Zend Lucene Search? I can use MySQL Text Search.</p>
		<p><strong>A</strong>. True, but for text search to work the table structure must be ISAM. ISAM tables don't support Transactions.
			Plus Zend search is more powerful than text search, and doesn't hit the database. And of course, you might want to index non-database assets such as PDFs, Word docs, images etc.</p>

		<h3>How to setup the Search module</h3>
		<?
			if(isset($msg)):
				echo '<p style="color:#f00">'.$msg.'</p>';
			endif;
		?>
		<p>To use the search module follow these steps:</p>
		<ul>
			<li>Add the search module to the modules folder</li>
			<li>Enable the search module in application/config  (<code>MODPATH.'search'</code>)</li>
			<li>Add Zend Search to the application/vendor folder. First, <a href="http://www.zend.com/community/downloads" title="Zend libraries">download</a> the zend libraries.
				The minimal package will suffice. You can copy the whole set of libraries to the vendor folder, though you only actually need Search, Loader and Exception classes.</li>
			<li>Add the <a href="http://codefury.net/projects/StandardAnalyzer/" title="StandardAnalyzer">StandardAnalyzer</a> to the application/vendor folder. This allows word <a href="http://en.wikipedia.org/wiki/Stemming" title="wikipedia article">stemming</a> - e.g. plurals.
				<u>Note</u>: this analyser is for English only. If you want to use this module for non-English languages, you don't need this library, but you will need to modify the config file to use the alternate analyser.
			</li>
			<li>create application/searchindex directory. To change the name/location of this folder, copy the config/search.php file to application/config and modify accordingly.</li>
		</ul>

		<p>Your folder structure should be as follows:</p>
		<code>
		<p>application / searchindex</p>
		<p>application / vendor / StandardAnalyzer</p>
		<p>application / vendor / Zend / Exception.php</p>
		<p>application / vendor / Zend / Loader</p>
		<p>application / vendor / Zend / Loader.php</p>
		<p>application / vendor / Zend / Search</p>
		</code>

		<h3>Search example</h3>

		<p><a href="/search_example/add" title="add some music">Add some music</a> to search against</p>
		<p>By selecting the above link, the following will be added to the search index:</p>
		<table border="0" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th>Artist</th>
					<th>Song Title</th>
					<th>Media (Model class)</th>
				</tr>
				<tr>
					<td>Ian Brown</td>
					<td>My Star</td>
					<td>MP3</td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Rolling Stones</td>
					<td>Brown Sugar</td>
					<td>MP3</td>
				</tr>
				<tr>
					<td>Stone Roses</td>
					<td>Sugar Spun Sister</td>
					<td>CD</td>
				</tr>
				<tr>
					<td>David Bowie</td>
					<td>Starman</td>
					<td>CD</td>
				</tr>
				<tr>
				<td>Bob Dylan</td>
				<td>Like a Rolling Stone</td>
				<td>MP3</td>
				</tr>
			</tbody>
		</table>
		<p>You should see some files in the <strong>searchindex</strong> folder. These are the index files</p>
		<p id="form">Try the following searches: 
			<a href="/search_example?q=stone#form">stone</a>, 
			<a href="/search_example?q=star#form">star</a>,
			<a href="/search_example?q=star*#form">star*</a>, 
			<a href="/search_example?q=sugar&amp;form=artists#form">sugar</a>, 
			<a href="/search_example?q=title:stone&amp;form=artists#form">title:stone</a>,
			<a href="/search_example?q=artist:stone&amp;form=artists#form">artist:stone</a>, 
			<a href="/search_example?q=type:cd and title:sugar&amp;form=artists#form">type:cd and title:sugar</a></p>
		<hr/>
		<form method="get" action="/search_example#form">
			<label for="q">search</label>
			<input type="hidden" name="form" value="artists" />			
			<input type="text" name="q" value="<? echo $query ?>" />
			<input type="submit" />
		</form>
		<hr/>

		<? if(isset($results)): ?>
		<table border="0" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th>Artist</th>
					<th>Song Title</th>
					<th>Relevance</th>
				</tr>
			</thead>
			<tbody>

			<? foreach($results as $hit):
				$score = round($hit->score, 2) *100;
			?>
			<tr>
				<td><? echo $hit->artist ?></td>
				<td><? echo $hit->title ?></td>
				<td><div class="graph"><div class="bar" style="width:<? echo $score ?>px"></div></div></td>
			</tr>
			<? endforeach; ?>

			</tbody>
		</table>
	   <? endif; ?>

		<h3>How to define content to add to the index</h3>
		<p>To add a Model to the search index, it must implement the <code>Searchable</code> interface. This interface is defined as follows:</p>
		<code><pre>
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
		</pre></code>

		<p>The search module includes an abstract ORM implementation of this interface, that implements all methods except <code>get_indexable_fields</code>
		<p>The <strong>identifier</strong> would likely be the Primary Key for an ORM Model. This is important, as when a record is retrieved from a search, it only contains indexed data,
			not all attributes. So, to display all Model attributes, it might be necessary to fetch the record by it's PK.</p>
		<p>The <strong>unique identifier</strong> must be unique to the Lucene index. If you are indexing more than one Model, PK's will not be unique. The ORM implementation uses both the PK and Model name to create a unique ID.
			The unique ID is required when updating/deleting an entry from the index.</p>
		<p>The <strong>type</strong> allows for search by Model type. See the example code in this distribution to see how this works. In the example above, the two media types - 'CD', and 'MP3' - are the class types, not attributes of the class.</p>
		<p>The <strong>get_indexable_fields</strong> method is the only complicated part to this solution. This method defines what fields to index, and what type of index to create.
			Essentially, there are 5 field types - different types are stored, indexed and tokenised. These field types are defined by Lucene and explained well in the <a href="http://framework.zend.com/manual/en/zend.search.lucene.html#zend.search.lucene.index-creation.understanding-field-types" title="Zend docs"> Zend documentation</a>.</p>
		<p>The <code>Searchable</code> interface defines constants mapping to these field types, along with another set of constants relating to HTML Decoding.</p>
		<p>For example, a blog Model might be defined as follows:</p>
<code><pre>
class Blog_Model extends Searchable_ORM {

	/**
	 * Searchable interface implementation
	 */
	public function get_indexable_fields() {
		$fields = array();
		$fields[] = new Search_Field('url', Searchable::UNINDEXED);
		$fields[] = new Search_Field('title', Searchable::TEXT, Searchable::DECODE_HTML);
		$fields[] = new Search_Field('content', Searchable::UNSTORED, Searchable::DECODE_HTML);
		$fields[] = new Search_Field('date', Searchable::UNINDEXED);
		return $fields;
	}
}
</pre></code>
	<p>Here, we are telling the index to:
		<ul>
			<li>Store the url, but not Index it. This is so we can display the blog url;</li>
			<li>Store, Index and Tokenise the title. This allow	s it to be searched against and displayed in the results;</li>
			<li>Index and Tokenise, but don't Store the content. This allows it to be searched against, but cannot be displayed in results</li>
			<li>Store, but don't Index the date, so we can display the date in the results</li>
		</ul>
		<p>We also tell the module to decode the HTML for the title and content prior to indexing. This allows HTML content to be indexed.
			If you have a CMS solution which allows HTML content to be stored this will be useful. The default is to not decode.</p>

<h3>How to add/remove content to the index</h3>
<p>Once you have defined your Models to implement the Searchable interface, adding content is simple, using the <strong>Search</strong> class.</p>
<p>To add a Model to the index:</p>
<code><pre>
$search = new Search;
$search->add($model);
</pre></code>

<p>To build a new index, build an array of indexable models. The index will be re-created fresh:</p>
<code><pre>
$search = new Search;
$search->build_search_index($models);
</pre></code>

<p>To update a Model, it is removed, then re-added to the index:</p>
<code><pre>
$search = new Search;
$search->update($model);
</pre></code>

<p>To delete a Model:</p>
<code><pre>
$search = new Search;
$search->remove($model);
</pre></code>

<h3>How to search the index</h3>
<p>This bit is really tricky ;-)</p>
<code><pre>
$search = new Search;
$search->find($query);
</pre></code>

<p>The example above gives a few ideas about how to query the index. I suggest reading the
	<a href="http://framework.zend.com/manual/en/zend.search.lucene.query-language.html" title="Zend docs">documentation</a>
	about all the possible ways to search using the query language - terms, fields, wildcards, ranges, booleans etc.
</p>
<h3>How to index other content</h3>
<p>Zend Search is capable of indexing web pages, PDFs, Word docs etc. The docs explain in detail how to do this. Here's an example that indexes the Kohana home page</p>
<p><a href="/search/addurl" title="add the Kohana home page">Add Kohana home page</a> to search against</p>
<hr/>
<p id="form2">Now try the following search: <a href="/search_example?q=kohana&amp;form=kohana#form2">kohana</a></p>
<hr/>
<form method="get" action="/search_example#form2">
	<label for="q">search</label>
	<input type="hidden" name="form" value="kohana" />
	<input type="text" name="q" value="<? echo $query ?>" />
	<input type="submit" />
</form>
<hr/>

<?
	if(isset($results2)) { 
		echo $results2;
		echo '<hr/>';
   }
?> 
<p><em>kosearch</em> is developed and maintained by <a href="http://www.badlydrawntoy.com" title="visit badlydrawntoy's blog">badlydrawntoy</a>
</div>
</body>
</html>