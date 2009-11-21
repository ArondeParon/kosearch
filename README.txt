kosearch iss a Search module for Kohana PHP. More specifically, it's an implementation of Zend (Lucene) Search, a file-based search/index solution. kosearch provides a simple way to index and search Models. It's perfect for a web site that might contain news, products etc. kosearch also exposes the underlying Zend libraries so that other things can be indexed - PDFs, web pages, Word docs etc.

The kosearch module has been written for, and tested against Kohana 2.3.4

The module includes a sample application that demonstrates and explains how to sue the module

To get up and running:

1. Copy the kosearch folder into your application/modules folder
2. Enable the search module in application/config (MODPATH.'search')
3. Run the example application, open your browser and navigate to http://your.app/search_example

The example explains where to download the necessary 3rd party Zend libraries etc.

kosearch is developed and maintained by badlydrawntoy - http://www.badlydrawntoy.com