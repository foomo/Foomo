<?php

/*
 * This file is part of the foomo Opensource Framework.
 *
 * The foomo Opensource Framework is free software: you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General Public License as
 * published  by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * The foomo Opensource Framework is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with
 * the foomo Opensource Framework. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Foomo;

/**
 * simple class for generating HTML, manipulating http headers and offering dynamic content to a browser cache
 * maybe change the api to jquery style
 * maybe change the api to simplexml style
 * how to ensure a forced order / priorities, when adding css links or js
 * that needs to be possible:
 *
 *	http://html5boilerplate.com/
 *		<!--[if lt IE 7]> <html lang="en-us" class="no-js ie6"> <![endif]-->
 *		<!-- JavaScript at the bottom, except for Modernizr -->
 *		<script src="//html5boilerplate.com/js/libs/modernizr-1.7.min.js"></script>
 *
 *
 * @link www.foomo.org
 * @license www.gnu.org/licenses/lgpl.txt
 * @author jan <jan@bestbytes.de>
 * @todo add js and css compiler
 * @todo maybe support haml and lesscss
 * @todo method chaining please
 * @todo remove csss ?!
 */
class HTMLDocument {
	private $indent = 0;
	private $docTypeString = '<!DOCTYPE html>';
	private $htmlOpeningTag = '<html>';
	private $metaData = array();
	private $styleSheetData = array();
	private $iECompatibilityMode;
	/**
	 * Should not be accesses directly use the methods instead
	 *
	 * @var array
	 */
	public $document;
	private $Etag, #http://www.w3.org/Protocols/rfc2616/rfc2616-sec13.html#sec13.3.3
	$expires,
	$lastModified,
	$cacheControl,
	$pragma;
	private $dynCssSheets = array();
	private $templates = array();
	private $javascripts = array();
	private $static = false;
	/**
	 * this picks up the If-None-Match from the header from the headers
	 * sent by the HTTP client {@link http://www.w3.org/Protocols/rfc2616/rfc2616-sec13.html#sec13.3.3 }
	 * @var string Etag
	 */
	private $browserCachedEtag = false;
	/**
	 * @var bool
	 */
	private $indentEnabled = true;
	private static $instance;

	/**
	 * singleton
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public static function getInstance()
	{

		if (!isset(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @todo restructure html by example
	 */
	public function __construct()
	{
		if (function_exists('apache_request_headers')) {
			$requestHeaders = apache_request_headers();
			if (isset($requestHeaders['If-None-Match'])) {
				$this->browserCachedEtag = trim($requestHeaders['If-None-Match']);
			}
		}

		$this->document['header'] = array();
		$this->document['head']['baseUrl'] = '';
		$this->document['head']['title'] = '';
		$this->document['head']['meta'] = '';
		$this->document['head']['metaRefresh'] = '';
		$this->document['head']['styleSheetLinks'] = '';
		$this->document['head']['styleSheets'] = '';
		$this->document['head']['javaScript'] = '';
		$this->document['head']['content'] = '';
		$this->document['body']['content'] = '';
		$this->document['body']['bgColor'] = '';
		$this->document['body']['style'] = '';
		$this->document['body']['class'] = '';
		$this->document['body']['backGround'] = '';
		$this->document['body']['leftMargin'] = '';
		$this->document['body']['topMargin'] = '';
		$this->document['body']['marginWidth'] = '';
		$this->document['body']['marginHeight'] = '';
		$this->document['body']['onLoad'] = '';
	}
	
	const IE_COMPATIBILITY_MODE_IE7 = 'IE=7';
	const IE_COMPATIBILITY_MODE_IE7_EMULATE = 'EmulateIE7';
	const IE_COMPATIBILITY_MODE_IE8 = 'IE=8';
	const IE_COMPATIBILITY_MODE_IE8_EMULATE = 'EmulateIE8';
	const IE_COMPATIBILITY_MODE_EDGE = 'edge';
	
	public function setIECompatibilityMode($mode)
	{
		$this->iECompatibilityMode = $mode;
	}
	
	/**
	 * set the docType (if you know what yut are doing)
	 *
	 * @param string $docType doc type
	 * 
	 * @return \Foomo\HTMLDocument
	 */
	public function setDocType($docType)
	{
		$this->docTypeString = $docType;
		return $this;
	}

	/**
	 * get the doc type
	 *
	 * @return string doc type
	 */
	public function getDocType()
	{
		return $this->docTypeString;
	}

	/**
	 * set the html opening tag
	 *
	 * @param string $htmlOpeningTag html opening tag
	 * 
	 * @return \Foomo\HTMLDocument
	 */
	public function setHTMLOpeningTag($htmlOpeningTag)
	{
		$this->htmlOpeningTag = $htmlOpeningTag;
		return $this;
	}

	/**
	 * get the html opening tag
	 *
	 * @return string html opening tag
	 */
	public function getHTMLOpeningTag()
	{
		return $this->htmlOpeningTag;
	}

	/**
	 * increment the source indentation
	 *
	 * @param integer $indentToInc
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public function incIndent($indentToInc)
	{
		$this->incIndent += $indentToInc;
		return $this;
	}

	/**
	 * decrement the source indentation
	 *
	 * @param integer $indentToInc
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public function decIndent($indentToDec)
	{
		$this->indent -= $indentToDec;
		if ($this->indent < 0) {
			$this->indent = 0;
		}
		return $this;
	}

	/**
	 * have a block of html indented
	 *
	 * @param string $block
	 * @param integer $indent
	 * @return string
	 */
	public function indentBlock($block, $indent = null)
	{
		if (!$this->indentEnabled) {
			return $block;
		}
		if (!isset($indent)) {
			$indent = $this->getIndent();
		}
		return $indent . implode(PHP_EOL . $indent, explode(PHP_EOL, $block));
	}

	private function getIndent()
	{
		if (!$this->indentEnabled) {
			return '';
		}
		return str_repeat('	', $this->indent);
	}

	/**
	 * add javascript code to the onLoad event
	 *
	 * @param string $javascript
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public function addOnLoad($javascript)
	{
		$this->document['body']['onLoad'] .= $javascript;
		return $this;
	}

	/**
	 * reset the onload attribute of the body element
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public function resetOnload()
	{
		$this->setBodyAttribute('onLoad', '');
		return $this;
	}

	/**
	 * set the contents of a give body attribute
	 *
	 * @param string $attributeName
	 * @param string $attributeValue
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public function setBodyAttribute($attributeName, $attributeValue)
	{
		$this->document['body'][$attributeName] = $attributeValue;
		return $this;
	}

	/**
	 * append to the contents of a give body attribute
	 *
	 * @param string $attributeName
	 * @param string $attributeValue
	 * @return \Foomo\HTMLDocument
	 */
	public function appendToBodyAttribute($attributeName, $attributeValue)
	{
		if (isset($this->document['body'][$attributeName])) {
			$this->document['body'][$attributeName] .= $attributeValue;
		} else {
			trigger_error('unknown body attributeName', E_USER_WARNING);
		}
		return $this;
	}

	/**
	 * Add HTML to the body of the HTML Document
	 *
	 * @param string $HTML arbitrary HTML - we are NOT validating what you add
	 * @see Foomo\HTMLDocument::indentBlock()
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public function addBody($HTML)
	{
		$this->document['body']['content'] .= $HTML;
		return $this;
	}

	/**
	 * Add HTML directly to the head
	 *
	 * @param string $HTML
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public function addHead($HTML)
	{
		$this->document['head']['content'] .= $HTML;
		return $this;
	}

	/**
	 * add a javascript string to the head - if you want to link to an external Javascript file use @see Foomo\HTMLDocument::addJavascripts
	 *
	 * @see Foomo\HTMLDocument::addJavascripts()
	 * @param string $javascript
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public function addJavascript($javascript)
	{
		$this->document['head']['javaScript'] .= $javascript;
		return $this;
	}

	/**
	 * add links to external Javascript files
	 *
	 * @example <code>$bert->HTMLDocument->addJavascripts(array('/tm/js/script.js', 'anotherscript.js'));</code>
	 * @see Foomo\HTMLDocument::addJavascript()
	 * @param array $jsLinks
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public function addJavascripts($jsLinks)
	{
		foreach ($jsLinks as $jsLink) {
			if (!in_array($jsLink, $this->javascripts)) {
				array_push($this->javascripts, $jsLink);
			}
		}
		return $this;
	}

	/**
	 * Set a meta refresh for the document - maybe for a Javascript free slideshow?
	 *
	 * @param integer $time in seconds
	 * @param string $location the location the browser should go to
	 * @return \Foomo\HTMLDocument
	 */
	public function setRefresh($time, $location)
	{
		$this->document['head']['metaRefresh'] = '<meta http-equiv="refresh" content="' . $time . ';URL=' . $location . '">';
		return $this;
	}

	/**
	 * set the title of you document
	 *
	 * @param string $title
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public function setTitle($title)
	{
		$this->document['head']['title'] = '<title>' . $title . '</title>' . PHP_EOL;
		return $this;
	}

	/**
	 * check if the document has been declared "static" e.g. if browser should be allowed to cache it
	 *
	 * @return boolean
	 */
	public function getStatic()
	{
		return $this->static;
	}

	/**
	 * magic to string
	 *
	 * @return string
	 * @see Foomo\HTMLDocument::output()
	 */
	public function __tostring()
	{
		return $this->output();
	}

	/**
	 * Set the base url of the page. This is useful, if you for example want to export html, that would still link to resources on your webserver
	 *
	 * @param string $url
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public function setBaseUrl($url)
	{
		$this->document['head']['baseUrl'] = $url;
		return $this;
	}

	/**
	 * like output , but the closing </body></html> tags are cut
	 *
	 * @return string
	 */
	public function outputWithOpenBody()
	{
		$doc = $this->output(true);
		return substr($doc, 0, strlen($doc) - strlen('</body></html>'));
	}

	/**
	 * get the HTML of the document as a string
	 * if the document has been made static by using
	 * this::makeStatic() headers will be sent to the browser
	 * @param boolean $suppressHeaders if true headers will not be sent
	 *
	 * @return string HTML od the Document
	 */
	public function output($suppressHeaders = false)
	{
		$meta = array();
		$tagSuffix = '>';
		$output = '';
		$this->document['head']['meta'] .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';

		$this->addMeta($meta);
		foreach ($this->metaData as $key => $val) {
			$this->document['head']['meta'] .= '<meta name="' . $key . '" content="' . $val . '"' . $tagSuffix . PHP_EOL;
		}
		$this->javascripts = array_unique($this->javascripts);
		foreach ($this->javascripts as $jsLink) {
			$this->document['head']['meta'] .= '<script language="JavaScript" src="' . $jsLink . '" type="text/javascript"></script>' . PHP_EOL;
		}
		if (!empty($this->document['head']['baseUrl'])) {
			$this->document['head']['baseUrl'] = '<base href="' . $this->document['head']['baseUrl'] . '" ' . $tagSuffix . PHP_EOL;
		}
		foreach ($this->dynCssSheets as $dynCssSheet) {
			$this->addStylesheet($dynCssSheet->render());
		}
		foreach (array_unique($this->styleSheetData) as $val) {
			$this->document['head']['styleSheetLinks'] .= '<link rel="stylesheet" href="' . $val . '" type="text/css"' . $tagSuffix . PHP_EOL;
		}
		foreach ($this->templates as $template) {
			$this->addBody($template->render());
		}

		$output .= $this->docTypeString . PHP_EOL;
		if(!empty($this->htmlOpeningTag)) {
			$output .= $this->htmlOpeningTag . PHP_EOL;
		}
		$output .= '<head>' . PHP_EOL;
		if(!empty($this->iECompatibilityMode)) {
			$output .= '<meta http-equiv="X-UA-Compatible" content="'. $this->iECompatibilityMode .'"/>' . PHP_EOL;
		}
		foreach ($this->document as $docPartName => $docPartArray) {
			switch ($docPartName) {
				case'header':
					foreach ($docPartArray as $header) {
						header($header);
					}
					break;
				case'head':
					foreach ($docPartArray as $headPartName => $headPart) {
						switch ($headPartName) {
							case'styleSheets':
								if (strlen($headPart) > 0) {
									$output .= '<style type="text/css">' . PHP_EOL . '<!--' .
											$headPart . PHP_EOL .
											'-->' . PHP_EOL . '</style>' . PHP_EOL;
								}
								break;
							case'javaScript':
								if (strlen($headPart) > 0) {
									$output .= '<script language="JavaScript" type="text/javascript">' . PHP_EOL . '// <![CDATA[ <!--' . PHP_EOL .
											$headPart .
											PHP_EOL . '// --> ]]>' . PHP_EOL . '</script>' . PHP_EOL;
									;
								}
								break;
							default:
								$output .= $headPart;
						}
					}
					break;
				case'body':
					$output .= '</head>' . PHP_EOL . '<body';
					foreach ($docPartArray as $bodyPartName => $bodyPart) {
						switch ($bodyPartName) {
							case'content':
								$bodyContent = '>' . PHP_EOL . $bodyPart;
								break;
							default:
								if (strlen($bodyPart) > 0) {
									$output .= ' ' . strtolower($bodyPartName) . '="' . $bodyPart . '"';
								}
						}
					}
					$output .= $bodyContent . '</body>';
					break;
				case'frameset':
					$output .= '</head>' . PHP_EOL;
					foreach ($docPartArray as $docPart) {
						$output .= $docPart;
					}
					break;
			}
		}
		$output .= '</html>';
		if ($this->static === true && !$suppressHeaders) {
			$this->sendStaticHeaders();
			//header('Content-Length: '.strLen($output));
		}
		return $output;
	}
	/**
	 * send http headers
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public function sendStaticHeaders()
	{
		session_cache_limiter('public');
		//correct headers
		/*
		  header tweaking has many parts ...
		  php.ini:
		  expose_php=off;
		 */
		if (isset($this->pragma)) {
			header('Pragma: ' . $this->pragma);
		} else {
			header('Pragma:');
		}
		if (isset($this->expires)) {
			header('Expires: ' . date('D, d M Y h:m:s', $this->expires) . ' GMT');
		}
		if (isset($this->Etag)) {
			header('Etag: ' . $this->Etag);
		}
		if (isset($this->lastModified)) {
			header('Last-Modified: ' . date('D, d M Y h:m:s ', $this->lastModified) . ' GMT');
		}
		if (isset($this->cacheControl)) {
			header('Cache-Control: ' . $this->cacheControl);
		} else {
			header('Cache-Control: max-age=' . (24 * 60 * 60) . ', private');
		}
		return $this;
	}

	/**
	 * Add meta information to to the head of the document
	 *
	 * @example $doc->addMeta(array('keywords' => 'super, great, wonderful', 'description' => 'this is a wonderful page'));
	 * @param array $meta arra('nameOfMetaEntry' => 'valueOfMetaEntry')
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public function addMeta($meta)
	{
		$this->metaData = array_merge($this->metaData, $meta);
		return $this;
	}

	/**
	 * Add a CSS Stylesheet String to the head of your document
	 *
	 * @see Foomo\HTMLDocument::addStylesheets()
	 * @param string $styleString CSS Style definition
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public function addStylesheet($styleString)
	{
		$this->document['head']['styleSheets'] .= PHP_EOL . $styleString;
		return $this;
	}

	/**
	 * Add a links to external CSS Stylesheet files to the head of your document
	 *
	 * @see Foomo\HTMLDocument::addStylesheet()
	 * @example <code>$bert->HTMLDocument->addStylesheets(array('my.css', 'path/to/my/other.css'))</code>
	 * @param array $styleString CSS Style definition
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public function addStylesheets($stylesheets)
	{
		$this->styleSheetData = array_merge($this->styleSheetData, $stylesheets);
		return $this;
	}

	/**
	 * Add HTML to a frameset. If you call this function you turn the document into a frameset
	 *
	 * @param string $html
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public function addFrameset($html)
	{
		if (isset($this->document['body'])) {
			unset($this->document['body']);
			$this->document['frameset'] = array();
			$this->document['frameset']['content'] = '';
			$this->document['frameset']['end'] = PHP_EOL;
		}
		$this->document['frameset']['content'] .= $html;
		return $this;
	}
	/**
	 * @param string $newPragma
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public function setPragma($newPragma)
	{
		$this->pragma = $newPragma;
		return $this;
	}
	/**
	 * @param string $newEtag
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public function setEtag($newEtag)
	{
		$this->Etag = $newEtag;
		return $this;
	}
	/**
	 * @param string $expiryDate
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public function setExpires($expiryDate)
	{
		$this->expires = $expiryDate;
		return $this;
	}

	public function makeStatic()
	{
		$this->static = true;
	}

	public function makeDynamic()
	{
		$this->static = false;
	}
	/**
	 * @param string $cacheControl
	 * @return \Foomo\HTMLDocument
	 */
	public function setCacheControl($cacheControl)
	{
		$this->cacheControl = $cacheControl;
		return $this;
	}
	/**
	 *
	 * @param integer $lastModified unix timestamp of last mod
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public function setLastModified($lastModified)
	{
		$this->lastModified = $lastModified;
		return $this;
	}

	/**
	 * @todo implement it
	 * @internal this is a draft / reminder
	 * @param type $Etag
	 */
	public function tryBrowserCache($Etag)
	{
		if ($this->static) {
			Http\BrowserCache::setResourceData('text/html', $this->Etag, $this->lastModified, null, $this->expires);
			if(Http\BrowserCache::tryBrowserCache()) {
				exit;
			} else {
				Http\BrowserCache::sendHeaders();
			}
		}
	}

	/**
	 * set a favicon note favicon may also be (animated) gifs
	 *
	 * @param string $pathToFavIcon /path/to/your/favicon.gif or .ico
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public function setFavIcon($pathToFavIcon)
	{
		$this->addHead('<link rel="icon" href="' . $pathToFavIcon . '" />');
		return $this;
	}

	/**
	 * Add a W3C HTML Validation Link to your page at the position of the current cursor
	 * just a nice gadget for development
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public function addW3CHTMLValidationLink()
	{
		$this->addBody('<a href="http://validator.w3.org/check?uri=referer"><img border="0" src="http://www.w3.org/Icons/valid-html401" alt="validation" title="validation" height="31" width="88"></a>');
		return $this;
	}

	public function enableIndent()
	{
		$old = $this->indentEnabled;
		$this->indentEnabled = true;
		return $old;
	}

	public function disableIndent()
	{
		$old = $this->indentEnabled;
		$this->indentEnabled = false;
		return $old;
	}

	/**
	 * Adds a dynamic stylesheet to the document
	 * .someClass, #someElementId { width : %someValue%; height : %anotherValue% }
	 *
	 * @param string $name
	 * @param string $filename
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public function addDynCssSheet($name, $filename)
	{
		if (file_exists($filename)) {
			$this->dynCssSheets[$name] = new Template($name, $filename);
		} else {
			trigger_error(__METHOD__ . ' file does not exist ' . $filename, E_USER_NOTICE);
		}
		return $this;
	}

	/**
	 * Set a value within a dynamic stylesheet
	 *
	 * @see Foomo\HTMLDocument::addDynCssSheet
	 *
	 * @param string $dynSheetName name of the sheet previously added
	 * @param string $name name of the value
	 * @param string $value value itself
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public function setDynCssSheetValue($dynSheetName, $name, $value)
	{
		$this->dynCssSheets[$dynSheetName]->setValue($name, $value);
		return $this;
	}

	/**
	 * @param string $url
	 *
	 * @return \Foomo\HTMLDocument
	 */
	public function setCanonicalLink($url)
	{
		$this->addHead('<link rel="canonical" href="' . \htmlspecialchars($url) . '">');
		return $this;
	}

}
