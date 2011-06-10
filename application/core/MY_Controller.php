<?php
/**Most controllers should extend this one.
 *
 * @copyright Copyright 2011 The Open University.
 */
//Common parameter names, shared by oEmbed/ embed/ popup controllers.
define('OUP_PARAM_DEBUG', '_debug');
define('OUP_PARAM_THEME', '_theme');
define('OUP_PARAM_LANG', 'lang');


class MY_Controller extends CI_Controller {

  public $firephp;
  protected $_request;

  public function __construct() {
    parent::__construct();

    $this->_request = (object) array(
      'debug' =>(bool)$this->input->get(OUP_PARAM_DEBUG),
      'theme' => $this->input->get(OUP_PARAM_THEME),
      'hide_controls'=>(bool)$this->input->get('_hide_controls'),
    );

    $this->load->library('FirePHPCore/Firephp');
    if ($this->config->item('debug') && $this->_request->debug) {
        #$this->load->library('FirePHPCore/FirephpEx', null, 'firephp');
    } else {
        $this->firephp->setEnabled(false);
        #$this->load->library('firephp_fake', null, 'firephp');
        #$this->firephp =& $this->firephp_fake;
    }
    $this->firephp->fb(__METHOD__, 'OUP', 'INFO');
    $this->firephp->log('test');

    $this->lang->initialize();
  }

  /** Get optional parameters for iframe URL (http_build_query)
  */
  public function options_build_query() {
    $params = '?';
	if ($this->_request->theme) {
	  $params .= '&'. OUP_PARAM_THEME .'='.$this->_request->theme;
	}
	if ($this->_request->debug) {
	  $params .= '&'. OUP_PARAM_DEBUG .'='.$this->_request->debug;
	}
	/*if ($this->_request->lang) {
	  $params .= '&'. OUP_PARAM_LANG .'='.$this->_request->lang;
	}*/
	if ($this->_request->hide_controls) {
	  $params .= '&'. '_hide_controls' .'='.$this->_request->hide_controls;
	}
	return $params;
  }

  /** Handle fatal errors.
  */
  public function _error($message, $code=500, $from=null) { #Was: protected.
    #$this->firephp->fb("$code: $message", $from, 'ERROR');
    $this->_log('error', "$from: $code, $message");
    @header("HTTP/1.1 $code");
    // For now, just output plain text.
    die("$code. Error, $message");
  }

  public function _log($level='error', $message, $php_error=FALSE) {
    $_CI = $this;
    $_CI->load->library('user_agent');
    $ip = $_SERVER['REMOTE_ADDR'];
    $ref= $_CI->agent->referrer();    #['HTTP_REFERER']
    $ua = $_CI->agent->agent_string();#['HTTP_USER_AGENT']
    $request = $_CI->uri->uri_string().'?'.$_SERVER['QUERY_STRING'];
    $msg = "$message, $request -- $ip, $ref, $ua";
    log_message($level, $msg);  #, $php_error);


    $fp_level = 'error'==$level ? 'ERROR' : 'INFO';
    $fp_label = 'error'==$level ? 'Error log' : 'Log';
    $this->firephp->fb($msg, $fp_label, $fp_level);
  }

  /** Handle required GET parameters. */
  protected function _required($param) {
    $value = $this->input->get($param);
    if (!$value) {
      $this->_error("'$param' is a required URL parameter.", 400);
    }
    return $value;
  }

  /** Make relative URLs absolute. */
  protected function _absolute($url, $base_url) {
    if ($url && !parse_url($url, PHP_URL_SCHEME)) {
      return $base_url.'/'.$url;
    }
    return $url;
  }

}
