<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2018  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

/**
 * MimeTypes
 *
 * @package  Helpers
 * @author   Ramon Barros <ramon@lideresistemas.com.br>
 */

if (!function_exists('get_mimes'))
{
    /**
     * Retorna os mime types dos arquivos
     *
     * @param  mixed
     * @return void
     */
    function get_mimes()
    {
        /*
        | -------------------------------------------------------------------
        | MIME TYPES
        | -------------------------------------------------------------------
        | This file contains an array of mime types.  It is used by the
        | Upload class to help identify allowed file types.
        |
        */
        return array(
            'hqx'	=>	array('application/mac-binhex40', 'application/mac-binhex', 'application/x-binhex40', 'application/x-mac-binhex40'),
            'cpt'	=>	'application/mac-compactpro',
            'csv'	=>	array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain'),
            'bin'	=>	array('application/macbinary', 'application/mac-binary', 'application/octet-stream', 'application/x-binary', 'application/x-macbinary'),
            'dms'	=>	'application/octet-stream',
            'lha'	=>	'application/octet-stream',
            'lzh'	=>	'application/octet-stream',
            'exe'	=>	array('application/octet-stream', 'application/x-msdownload'),
            'class'	=>	'application/octet-stream',
            'psd'	=>	array('application/x-photoshop', 'image/vnd.adobe.photoshop'),
            'so'	=>	'application/octet-stream',
            'sea'	=>	'application/octet-stream',
            'dll'	=>	'application/octet-stream',
            'oda'	=>	'application/oda',
            'pdf'	=>	array('application/pdf', 'application/force-download', 'application/x-download', 'binary/octet-stream'),
            'ai'	=>	array('application/pdf', 'application/postscript'),
            'eps'	=>	'application/postscript',
            'ps'	=>	'application/postscript',
            'smi'	=>	'application/smil',
            'smil'	=>	'application/smil',
            'mif'	=>	'application/vnd.mif',
            'xls'	=>	array('application/vnd.ms-excel', 'application/msexcel', 'application/x-msexcel', 'application/x-ms-excel', 'application/x-excel', 'application/x-dos_ms_excel', 'application/xls', 'application/x-xls', 'application/excel', 'application/download', 'application/vnd.ms-office', 'application/msword'),
            'ppt'	=>	array('application/powerpoint', 'application/vnd.ms-powerpoint', 'application/vnd.ms-office', 'application/msword'),
            'pptx'	=> 	array('application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/x-zip', 'application/zip'),
            'wbxml'	=>	'application/wbxml',
            'wmlc'	=>	'application/wmlc',
            'dcr'	=>	'application/x-director',
            'dir'	=>	'application/x-director',
            'dxr'	=>	'application/x-director',
            'dvi'	=>	'application/x-dvi',
            'gtar'	=>	'application/x-gtar',
            'gz'	=>	'application/x-gzip',
            'gzip'  =>	'application/x-gzip',
            'php'	=>	array('application/x-httpd-php', 'application/php', 'application/x-php', 'text/php', 'text/x-php', 'application/x-httpd-php-source'),
            'php4'	=>	'application/x-httpd-php',
            'php3'	=>	'application/x-httpd-php',
            'phtml'	=>	'application/x-httpd-php',
            'phps'	=>	'application/x-httpd-php-source',
            'js'	=>	array('application/x-javascript', 'text/plain'),
            'swf'	=>	'application/x-shockwave-flash',
            'sit'	=>	'application/x-stuffit',
            'tar'	=>	'application/x-tar',
            'tgz'	=>	array('application/x-tar', 'application/x-gzip-compressed'),
            'z'	=>	'application/x-compress',
            'xhtml'	=>	'application/xhtml+xml',
            'xht'	=>	'application/xhtml+xml',
            'zip'	=>	array('application/x-zip', 'application/zip', 'application/x-zip-compressed', 'application/s-compressed', 'multipart/x-zip'),
            'rar'	=>	array('application/x-rar', 'application/rar', 'application/x-rar-compressed'),
            'mid'	=>	'audio/midi',
            'midi'	=>	'audio/midi',
            'mpga'	=>	'audio/mpeg',
            'mp2'	=>	'audio/mpeg',
            'mp3'	=>	array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'),
            'aif'	=>	array('audio/x-aiff', 'audio/aiff'),
            'aiff'	=>	array('audio/x-aiff', 'audio/aiff'),
            'aifc'	=>	'audio/x-aiff',
            'ram'	=>	'audio/x-pn-realaudio',
            'rm'	=>	'audio/x-pn-realaudio',
            'rpm'	=>	'audio/x-pn-realaudio-plugin',
            'ra'	=>	'audio/x-realaudio',
            'rv'	=>	'video/vnd.rn-realvideo',
            'wav'	=>	array('audio/x-wav', 'audio/wave', 'audio/wav'),
            'bmp'	=>	array('image/bmp', 'image/x-bmp', 'image/x-bitmap', 'image/x-xbitmap', 'image/x-win-bitmap', 'image/x-windows-bmp', 'image/ms-bmp', 'image/x-ms-bmp', 'application/bmp', 'application/x-bmp', 'application/x-win-bitmap'),
            'gif'	=>	'image/gif',
            'jpeg'	=>	array('image/jpeg', 'image/pjpeg'),
            'jpg'	=>	array('image/jpeg', 'image/pjpeg'),
            'jpe'	=>	array('image/jpeg', 'image/pjpeg'),
            'jp2'	=>	array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
            'j2k'	=>	array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
            'jpf'	=>	array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
            'jpg2'	=>	array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
            'jpx'	=>	array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
            'jpm'	=>	array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
            'mj2'	=>	array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
            'mjp2'	=>	array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
            'png'	=>	array('image/png',  'image/x-png'),
            'tiff'	=>	'image/tiff',
            'tif'	=>	'image/tiff',
            'css'	=>	array('text/css', 'text/plain'),
            'html'	=>	array('text/html', 'text/plain'),
            'htm'	=>	array('text/html', 'text/plain'),
            'shtml'	=>	array('text/html', 'text/plain'),
            'txt'	=>	'text/plain',
            'text'	=>	'text/plain',
            'log'	=>	array('text/plain', 'text/x-log'),
            'rtx'	=>	'text/richtext',
            'rtf'	=>	'text/rtf',
            'xml'	=>	array('application/xml', 'text/xml', 'text/plain'),
            'xsl'	=>	array('application/xml', 'text/xsl', 'text/xml'),
            'mpeg'	=>	'video/mpeg',
            'mpg'	=>	'video/mpeg',
            'mpe'	=>	'video/mpeg',
            'qt'	=>	'video/quicktime',
            'mov'	=>	'video/quicktime',
            'avi'	=>	array('video/x-msvideo', 'video/msvideo', 'video/avi', 'application/x-troff-msvideo'),
            'movie'	=>	'video/x-sgi-movie',
            'doc'	=>	array('application/msword', 'application/vnd.ms-office'),
            'docx'	=>	array('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/msword', 'application/x-zip'),
            'dot'	=>	array('application/msword', 'application/vnd.ms-office'),
            'dotx'	=>	array('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/msword'),
            'xlsx'	=>	array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/vnd.ms-excel', 'application/msword', 'application/x-zip'),
            'word'	=>	array('application/msword', 'application/octet-stream'),
            'xl'	=>	'application/excel',
            'eml'	=>	'message/rfc822',
            'json'  =>	array('application/json', 'text/json'),
            'pem'   =>	array('application/x-x509-user-cert', 'application/x-pem-file', 'application/octet-stream'),
            'p10'   =>	array('application/x-pkcs10', 'application/pkcs10'),
            'p12'   =>	'application/x-pkcs12',
            'p7a'   =>	'application/x-pkcs7-signature',
            'p7c'   =>	array('application/pkcs7-mime', 'application/x-pkcs7-mime'),
            'p7m'   =>	array('application/pkcs7-mime', 'application/x-pkcs7-mime'),
            'p7r'   =>	'application/x-pkcs7-certreqresp',
            'p7s'   =>	'application/pkcs7-signature',
            'crt'   =>	array('application/x-x509-ca-cert', 'application/x-x509-user-cert', 'application/pkix-cert'),
            'crl'   =>	array('application/pkix-crl', 'application/pkcs-crl'),
            'der'   =>	'application/x-x509-ca-cert',
            'kdb'   =>	'application/octet-stream',
            'pgp'   =>	'application/pgp',
            'gpg'   =>	'application/gpg-keys',
            'sst'   =>	'application/octet-stream',
            'csr'   =>	'application/octet-stream',
            'rsa'   =>	'application/x-pkcs7',
            'cer'   =>	array('application/pkix-cert', 'application/x-x509-ca-cert'),
            '3g2'   =>	'video/3gpp2',
            '3gp'   =>	array('video/3gp', 'video/3gpp'),
            'mp4'   =>	'video/mp4',
            'm4a'   =>	'audio/x-m4a',
            'f4v'   =>	array('video/mp4', 'video/x-f4v'),
            'flv'	=>	'video/x-flv',
            'webm'	=>	'video/webm',
            'aac'   =>	'audio/x-acc',
            'm4u'   =>	'application/vnd.mpegurl',
            'm3u'   =>	'text/plain',
            'xspf'  =>	'application/xspf+xml',
            'vlc'   =>	'application/videolan',
            'wmv'   =>	array('video/x-ms-wmv', 'video/x-ms-asf'),
            'au'    =>	'audio/x-au',
            'ac3'   =>	'audio/ac3',
            'flac'  =>	'audio/x-flac',
            'ogg'   =>	array('audio/ogg', 'video/ogg', 'application/ogg'),
            'kmz'	=>	array('application/vnd.google-earth.kmz', 'application/zip', 'application/x-zip'),
            'kml'	=>	array('application/vnd.google-earth.kml+xml', 'application/xml', 'text/xml'),
            'ics'	=>	'text/calendar',
            'ical'	=>	'text/calendar',
            'zsh'	=>	'text/x-scriptzsh',
            '7zip'	=>	array('application/x-compressed', 'application/x-zip-compressed', 'application/zip', 'multipart/x-zip'),
            'cdr'	=>	array('application/cdr', 'application/coreldraw', 'application/x-cdr', 'application/x-coreldraw', 'image/cdr', 'image/x-cdr', 'zz-application/zz-winassoc-cdr'),
            'wma'	=>	array('audio/x-ms-wma', 'video/x-ms-asf'),
            'jar'	=>	array('application/java-archive', 'application/x-java-application', 'application/x-jar', 'application/x-compressed'),
            'svg'	=>	array('image/svg+xml', 'application/xml', 'text/xml'),
            'vcf'	=>	'text/x-vcard',
            'srt'	=>	array('text/srt', 'text/plain'),
            'vtt'	=>	array('text/vtt', 'text/plain'),
            'ico'	=>	array('image/x-icon', 'image/x-ico', 'image/vnd.microsoft.icon')
        );
    }
}

/**
 * MimeTypes
 *
 * @package  Helpers
 * @author   Ramon Barros <ramon@lideresistemas.com.br>
 */

if (!function_exists('sanitize_filename'))
{
    /**
     * Retorna os mime types dos arquivos
     *
     * @param  mixed
     * @return void
     */
    function sanitize_filename($str, $relative_path = FALSE)
    {
        $bad = array(
            '../', '<!--', '-->', '<', '>',
            "'", '"', '&', '$', '#',
            '{', '}', '[', ']', '=',
            ';', '?', '%20', '%22',
            '%3c',		// <
            '%253c',	// <
            '%3e',		// >
            '%0e',		// >
            '%28',		// (
            '%29',		// )
            '%2528',	// (
            '%26',		// &
            '%24',		// $
            '%3f',		// ?
            '%3b',		// ;
            '%3d'		// =
        );

        if ( ! $relative_path)
		{
			$bad[] = './';
			$bad[] = '/';
		}

		$str = remove_invisible_characters($str, FALSE);

		do
		{
			$old = $str;
			$str = str_replace($bad, '', $str);
		}
		while ($old !== $str);

		return stripslashes($str);
    }
}

if ( ! function_exists('remove_invisible_characters'))
{
	/**
	 * Remove Invisible Characters
	 *
	 * This prevents sandwiching null characters
	 * between ascii characters, like Java\0script.
	 *
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	function remove_invisible_characters($str, $url_encoded = TRUE)
	{
		$non_displayables = array();

		// every control character except newline (dec 10),
		// carriage return (dec 13) and horizontal tab (dec 09)
		if ($url_encoded)
		{
			$non_displayables[] = '/%0[0-8bcef]/';	// url encoded 00-08, 11, 12, 14, 15
			$non_displayables[] = '/%1[0-9a-f]/';	// url encoded 16-31
		}

		$non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';	// 00-08, 11, 12, 14-31, 127

		do
		{
			$str = preg_replace($non_displayables, '', $str, -1, $count);
		}
		while ($count);

		return $str;
	}
}

if ( ! function_exists('is_php'))
{
	/**
	 * Determines if the current version of PHP is equal to or greater than the supplied value
	 *
	 * @param	string
	 * @return	bool	TRUE if the current version is $version or higher
	 */
	function is_php($version)
	{
		static $_is_php;
		$version = (string) $version;

		if ( ! isset($_is_php[$version]))
		{
			$_is_php[$version] = version_compare(PHP_VERSION, $version, '>=');
		}

		return $_is_php[$version];
	}
}

if ( ! function_exists('is_really_writable'))
{
	/**
	 * Tests for file writability
	 *
	 * is_writable() returns TRUE on Windows servers when you really can't write to
	 * the file, based on the read-only attribute. is_writable() is also unreliable
	 * on Unix servers if safe_mode is on.
	 *
	 * @link	https://bugs.php.net/bug.php?id=54709
	 * @param	string
	 * @return	bool
	 */
	function is_really_writable($file)
	{
		// If we're on a Unix server with safe_mode off we call is_writable
		if (DIRECTORY_SEPARATOR === '/' && (is_php('5.4') OR ! ini_get('safe_mode')))
		{
			return is_writable($file);
		}

		/* For Windows servers and safe_mode "on" installations we'll actually
		 * write a file then read it. Bah...
		 */
		if (is_dir($file))
		{
			$file = rtrim($file, '/').'/'.md5(mt_rand());
			if (($fp = @fopen($file, 'ab')) === FALSE)
			{
				return FALSE;
			}

			fclose($fp);
			@chmod($file, 0777);
			@unlink($file);
			return TRUE;
		}
		elseif ( ! is_file($file) OR ($fp = @fopen($file, 'ab')) === FALSE)
		{
			return FALSE;
		}

		fclose($fp);
		return TRUE;
	}
}
