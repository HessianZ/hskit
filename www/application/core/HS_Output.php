<?php

/**
* HS_Output
* 
* @author Hessian <hess.4x@gmail.com>
 */
?>
<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class HS_Output extends CI_Output
{
    function __construct()
    {
        parent::__construct();
    }

    /**
    * Write a Cache File
    *
    * @access	public
    * @param 	string
    * @return	void
     */
    function _write_cache($output)
    {
        $CI =& get_instance();
        $path = $CI->config->item('cache_path');

        $cache_path = ($path == '') ? APPPATH.'cache/' : $path;

        if ( ! is_dir($cache_path) OR ! is_really_writable($cache_path))
        {
            log_message('error', "Unable to write cache file: ".$cache_path);
            return;
        }

        $uri =	$CI->config->item('base_url').
            $CI->config->item('index_page').
            $CI->uri->uri_string();

        // append querystring
        $qs = (empty($_SERVER['QUERY_STRING'])) ? '' : '?'.$_SERVER['QUERY_STRING'];
        $uri .= $qs;
        // append querystring  

        $cache_path .= md5($uri);

        if ( ! $fp = @fopen($cache_path, FOPEN_WRITE_CREATE_DESTRUCTIVE))
        {
            log_message('error', "Unable to write cache file: ".$cache_path);
            return;
        }

        $expire = time() + ($this->cache_expiration * 60);

        if (flock($fp, LOCK_EX))
        {
            fwrite($fp, $expire.'TS--->'.$output);
            flock($fp, LOCK_UN);
        }
        else
        {
            log_message('error', "Unable to secure a file lock for file at: ".$cache_path);
            return;
        }
        fclose($fp);
        @chmod($cache_path, FILE_WRITE_MODE);

        log_message('debug', "Cache file written: ".$cache_path);
    }

    // --------------------------------------------------------------------

    /**
    * Update/serve a cached file
    *
    * @access	public
    * @param 	object	config class
    * @param 	object	uri class
    * @return	void
     */
    function _display_cache(&$CFG, &$URI)
    {
        $cache_path = ($CFG->item('cache_path') == '') ? APPPATH.'cache/' : $CFG->item('cache_path');

        // Build the file path.  The file name is an MD5 hash of the full URI
        $uri =	$CFG->item('base_url').
            $CFG->item('index_page').
            $URI->uri_string;

        // append querystring
        $qs = (empty($_SERVER['QUERY_STRING'])) ? '' : '?'.$_SERVER['QUERY_STRING'];
        $uri .= $qs;
        // append querystring  

        $filepath = $cache_path.md5($uri);

        if ( ! @file_exists($filepath))
        {
            return FALSE;
        }

        if ( ! $fp = @fopen($filepath, FOPEN_READ))
        {
            return FALSE;
        }

        flock($fp, LOCK_SH);

        $cache = '';
        if (filesize($filepath) > 0)
        {
            $cache = fread($fp, filesize($filepath));
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        // Strip out the embedded timestamp
        if ( ! preg_match("/(\d+TS--->)/", $cache, $match))
        {
            return FALSE;
        }

        // Has the file expired? If so we'll delete it.
        if (time() >= trim(str_replace('TS--->', '', $match['1'])))
        {
            if (is_really_writable($cache_path))
            {
                @unlink($filepath);
                log_message('debug', "Cache file has expired. File deleted");
                return FALSE;
            }
        }

        // Display the cache
        $this->_display(str_replace($match['0'], '', $cache));
        log_message('debug', "Cache file is current. Sending it to browser.");
        return TRUE;
    }

    /**
    * Display Output
    *
    * All "view" data is automatically put into this variable by the controller class:
    *
    * $this->final_output
    *
    * This function sends the finalized output data to the browser along
    * with any server headers and profile data.  It also stops the
    * benchmark timer so the page rendering speed and memory usage can be shown.
    *
    * @access	public
    * @param 	string
    * @return	mixed
     */
    function _display($output = '')
    {
        try 
        {
            parent::_display( $output );
        }
        catch ( Exception $e )
        {
            $_error =& load_class('Exceptions', 'core');
            echo $_error->show_error($e->getMessage(), $e->getTraceAsString(), 'error_general', $e->getCode());
        }
    }

}
