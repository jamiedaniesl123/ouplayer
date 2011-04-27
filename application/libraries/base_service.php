<?php
/** Base class for all oEmbed service provider libraries.
 *
 * @copyright Copyright 2011 The Open University.
 */
//NDF, extracted from oembed controller, 24 Feb. 2011.

interface iService {
  /** call.
  * @return object Return a $meta meta-data object, as inserted in DB.
  */
  public function call($url, $regex_matches);
}

abstract class Base_service implements iService {

  protected $CI;

  public function __construct() {
    $this->CI =& get_instance();
  }

  protected function _http_request_curl($url, $spoof=TRUE) {
    if (!function_exists('curl_init'))  die('Error, cURL is required.');

    $ua = 'My client/0.1 (PHP/cURL)';
    if ($spoof) {
       $ua="Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-GB; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3";
    }

    $h_curl = curl_init($url);
    curl_setopt($h_curl, CURLOPT_USERAGENT, $ua);
    if (!$spoof) {
      curl_setopt($h_curl, CURLOPT_REFERER,   'http://example.org');
    }
	$http_proxy = $this->CI->config->item('http_proxy');
	if ($http_proxy) {
	  curl_setopt($h_curl, CURLOPT_PROXY, $http_proxy);
	}
    curl_setopt($h_curl, CURLOPT_RETURNTRANSFER, TRUE);
    $result = array('data' => curl_exec($h_curl));
    if ($errno = curl_errno($h_curl)) {
      die("Error: cURL $errno, ".curl_error($h_curl)." GET $url");
    }
    $result['info'] = curl_getinfo($h_curl);
    $result['success'] = ($result['info']['http_code'] < 300);
    return (object) $result;
  }
  
  protected function _http_request_json($url, $spoof=TRUE) {
    $result = $this->_http_request_curl($url, $spoof);
    if ($result->success) {
      $result->json = json_decode($result->data);
    }
    return $result;
  }

  protected function _safe_xml($xml) {
    $safe = array('&amp;', '&gt;', '&lt;', '&apos;', '&quot;');
    $place= array('#AMP#', '#GT#', '#LT#', '#APOS#', '#QUOT#');
    $result = str_replace($safe, $place, $xml);
    $result = preg_replace('@&[^#]\w+?;@', '', $result);
    $result = str_replace($place, $safe, $result);
    return $result;
  }

  /**Safely, recursively create directories.
   * Status: not working fully, on Windows.
   * Source: https://github.com/nfreear/moodle-filter_simplespeak/blob/master/simplespeaklib.php
   */
  function _mkdir_safe($base, $path, $perm=0777) { #Or 0664.
    $parts = explode('/', trim($path, '/'));
    $dir = $base;
    $success = true;
    foreach ($parts as $p) {
      $dir .= "/$p";
      if (is_dir($dir)) {
	    break;
      } elseif (file_exists($dir)) {
        #error("File exists '$p'.");
      }
      $success = mkdir($dir, $perm);
    }
    return $success;
  }
}


/* 'translate text' placeholder - Internationalization/ Localization.
 * See: cloudengine/libs./MY_Language; Drupal.
 */
function t($s) {
  $s = str_replace(array('<s>', '</s>'), array('<span>', '</span>'), $s);
  return /*Debug: '^'.*/$s;
}
if (!function_exists('_')) {
  function _($s) { return $s; }
}
