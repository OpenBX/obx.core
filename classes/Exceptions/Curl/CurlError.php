<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Core\Exceptions\Curl;

if(!defined('CURLE_NOT_BUILT_IN')) define('CURLE_NOT_BUILT_IN', 4);
if(!defined('CURLE_REMOTE_ACCESS_DENIED')) define('CURLE_REMOTE_ACCESS_DENIED', 9);
if(!defined('CURLE_FTP_ACCEPT_FAILED')) define('CURLE_FTP_ACCEPT_FAILED', 10);
if(!defined('CURLE_FTP_ACCEPT_TIMEOUT')) define('CURLE_FTP_ACCEPT_TIMEOUT', 12);
if(!defined('CURLE_FTP_COULDNT_SET_TYPE')) define('CURLE_FTP_COULDNT_SET_TYPE', 17);
if(!defined('CURLE_QUOTE_ERROR')) define('CURLE_QUOTE_ERROR', 21);
if(!defined('CURLE_HTTP_RETURNED_ERROR')) define('CURLE_HTTP_RETURNED_ERROR', 22);
if(!defined('CURLE_UPLOAD_FAILED')) define('CURLE_UPLOAD_FAILED', 25);
if(!defined('CURLE_OPERATION_TIMEDOUT')) define('CURLE_OPERATION_TIMEDOUT', 28);
if(!defined('CURLE_RANGE_ERROR')) define('CURLE_RANGE_ERROR', 33);
if(!defined('CURLE_BAD_DOWNLOAD_RESUME')) define('CURLE_BAD_DOWNLOAD_RESUME', 36);
if(!defined('CURLE_INTERFACE_FAILED')) define('CURLE_INTERFACE_FAILED', 45);
if(!defined('CURLE_UNKNOWN_OPTION')) define('CURLE_UNKNOWN_OPTION', 48);
if(!defined('CURLE_PEER_FAILED_VERIFICATION')) define('CURLE_PEER_FAILED_VERIFICATION', 51);
if(!defined('CURLE_USE_SSL_FAILED')) define('CURLE_USE_SSL_FAILED', 64);
if(!defined('CURLE_SEND_FAIL_REWIND')) define('CURLE_SEND_FAIL_REWIND', 65);
if(!defined('CURLE_SSL_ENGINE_INITFAILED')) define('CURLE_SSL_ENGINE_INITFAILED', 66);
if(!defined('CURLE_LOGIN_DENIED')) define('CURLE_LOGIN_DENIED', 67);
if(!defined('CURLE_TFTP_NOTFOUND')) define('CURLE_TFTP_NOTFOUND', 68);
if(!defined('CURLE_TFTP_PERM')) define('CURLE_TFTP_PERM', 69);
if(!defined('CURLE_REMOTE_DISK_FULL')) define('CURLE_REMOTE_DISK_FULL', 70);
if(!defined('CURLE_TFTP_ILLEGAL')) define('CURLE_TFTP_ILLEGAL', 71);
if(!defined('CURLE_TFTP_UNKNOWNID')) define('CURLE_TFTP_UNKNOWNID', 72);
if(!defined('CURLE_REMOTE_FILE_EXISTS')) define('CURLE_REMOTE_FILE_EXISTS', 73);
if(!defined('CURLE_TFTP_NOSUCHUSER')) define('CURLE_TFTP_NOSUCHUSER', 74);
if(!defined('CURLE_CONV_FAILED')) define('CURLE_CONV_FAILED', 75);
if(!defined('CURLE_CONV_REQD')) define('CURLE_CONV_REQD', 76);
if(!defined('CURLE_SSL_CACERT_BADFILE')) define('CURLE_SSL_CACERT_BADFILE', 77);
if(!defined('CURLE_REMOTE_FILE_NOT_FOUND')) define('CURLE_REMOTE_FILE_NOT_FOUND', 78);
if(!defined('CURLE_SSH')) define('CURLE_SSH', 79);
if(!defined('CURLE_SSL_SHUTDOWN_FAILED')) define('CURLE_SSL_SHUTDOWN_FAILED', 80);
if(!defined('CURLE_AGAIN')) define('CURLE_AGAIN', 81);
if(!defined('CURLE_SSL_CRL_BADFILE')) define('CURLE_SSL_CRL_BADFILE', 82);
if(!defined('CURLE_SSL_ISSUER_ERROR')) define('CURLE_SSL_ISSUER_ERROR', 83);
if(!defined('CURLE_FTP_PRET_FAILED')) define('CURLE_FTP_PRET_FAILED', 84);
if(!defined('CURLE_RTSP_CSEQ_ERROR')) define('CURLE_RTSP_CSEQ_ERROR', 85);
if(!defined('CURLE_RTSP_SESSION_ERROR')) define('CURLE_RTSP_SESSION_ERROR', 86);
if(!defined('CURLE_FTP_BAD_FILE_LIST')) define('CURLE_FTP_BAD_FILE_LIST', 87);
if(!defined('CURLE_CHUNK_FAILED')) define('CURLE_CHUNK_FAILED', 88);
if(!defined('CURLE_NO_CONNECTION_AVAILABLE')) define('CURLE_NO_CONNECTION_AVAILABLE', 89);

class CurlError extends RequestError {
	const _FILE_ = __FILE__;
	const LANG_PREFIX = 'CURLE_';

	const E_UNKNOWN_ERROR_CODE = 45000;

	// The URL you passed to libcurl used a protocol that this libcurl does not support.
	// The support might be a compile-time option that you didn't use,
	// it can be a misspelled protocol string or just a protocol libcurl has no code for
	const E_UNSUPPORTED_PROTOCOL = CURLE_UNSUPPORTED_PROTOCOL;

	// Very early initialization code failed.
	// This is likely to be an internal error or problem,
	// or a resource problem where something fundamental couldn't get done at init time.
	const E_FAILED_INIT = CURLE_FAILED_INIT;

	//The URL was not properly formatted.
	const E_URL_MALFORMAT = CURLE_URL_MALFORMAT;

	// A requested feature, protocol or option was not found built-in in this libcurl
	// due to a build-time decision. This means that a feature or option was not enabled or explicitly
	// disabled when libcurl was built and in order to get it to function you have to get a rebuilt libcurl.
	const E_NOT_BUILT_IN = CURLE_NOT_BUILT_IN;

	// Couldn't resolve proxy. The given proxy host could not be resolved.
	const E_COULDNT_RESOLVE_PROXY = CURLE_COULDNT_RESOLVE_PROXY;

	// Couldn't resolve host. The given remote host was not resolved.
	const E_COULDNT_RESOLVE_HOST = CURLE_COULDNT_RESOLVE_HOST;

	// Failed to connect() to host or proxy.
	const E_COULDNT_CONNECT = CURLE_COULDNT_CONNECT;

	// After connecting to a FTP server,
	// libcurl expects to get a certain reply back. This error code implies
	// that it got a strange or bad reply. The given remote server is probably not an OK FTP server.
	const E_FTP_WEIRD_SERVER_REPLY = CURLE_FTP_WEIRD_SERVER_REPLY;

	// We were denied access to the resource given in the URL. For FTP,
	// this occurs while trying to change to the remote directory.
	const E_REMOTE_ACCESS_DENIED = CURLE_REMOTE_ACCESS_DENIED;

	// While waiting for the server to connect back when an active FTP session is used,
	// an error code was sent over the control connection or similar.
	const E_FTP_ACCEPT_FAILED = CURLE_FTP_ACCEPT_FAILED;

	// After having sent the FTP password to the server, libcurl expects a proper reply.
	// This error code indicates that an unexpected code was returned.
	const E_FTP_WEIRD_PASS_REPLY = CURLE_FTP_WEIRD_PASS_REPLY;

	// During an active FTP session while waiting for the server to connect,
	// the CURLOPT_ACCEPTTIMOUT_MS (or the internal default) timeout expired.
	const E_FTP_ACCEPT_TIMEOUT = CURLE_FTP_ACCEPT_TIMEOUT;

	//libcurl failed to get a sensible result back from the server as a response to either a PASV or a EPSV command. The server is flawed.
	const E_FTP_WEIRD_PASV_REPLY = CURLE_FTP_WEIRD_PASV_REPLY;

	//FTP servers return a 227-line as a response to a PASV command. If libcurl fails to parse that line, this return code is passed back.
	const E_FTP_WEIRD_227_FORMAT = CURLE_FTP_WEIRD_227_FORMAT;

	//An internal failure to lookup the host used for the new connection.
	const E_FTP_CANT_GET_HOST = CURLE_FTP_CANT_GET_HOST;

	//Received an error when trying to set the transfer mode to binary or ASCII.
	const E_FTP_COULDNT_SET_TYPE = CURLE_FTP_COULDNT_SET_TYPE;

	//A file transfer was shorter or larger than expected. This happens when the server first reports an expected transfer size, and then delivers data that doesn't match the previously given size.
	const E_PARTIAL_FILE = CURLE_PARTIAL_FILE;

	//This was either a weird reply to a 'RETR' command or a zero byte transfer complete.
	const E_FTP_COULDNT_RETR_FILE = CURLE_FTP_COULDNT_RETR_FILE;

	//When sending custom "QUOTE" commands to the remote server, one of the commands returned an error code that was 400 or higher (for FTP) or otherwise indicated unsuccessful completion of the command.
	const E_QUOTE_ERROR = CURLE_QUOTE_ERROR;

	//This is returned if CURLOPT_FAILONERROR is set TRUE and the HTTP server returns an error code that is >= 400.
	const E_HTTP_RETURNED_ERROR = CURLE_HTTP_RETURNED_ERROR;

	//An error occurred when writing received data to a local file, or an error was returned to libcurl from a write callback.
	const E_WRITE_ERROR = CURLE_WRITE_ERROR;

	//Failed starting the upload. For FTP, the server typically denied the STOR command. The error buffer usually contains the server's explanation for this.
	const E_UPLOAD_FAILED = CURLE_UPLOAD_FAILED;

	//There was a problem reading a local file or an error returned by the read callback.
	const E_READ_ERROR = CURLE_READ_ERROR;

	//A memory allocation request failed. This is serious badness and things are severely screwed up if this ever occurs.
	const E_OUT_OF_MEMORY = CURLE_OUT_OF_MEMORY;

	//Operation timeout. The specified time-out period was reached according to the conditions.
	const E_OPERATION_TIMEDOUT = CURLE_OPERATION_TIMEDOUT;

	//The FTP PORT command returned error. This mostly happens when you haven't specified a good enough address for libcurl to use. See CURLOPT_FTPPORT.
	const E_FTP_PORT_FAILED = CURLE_FTP_PORT_FAILED;

	//The FTP REST command returned error. This should never happen if the server is sane.
	const E_FTP_COULDNT_USE_REST = CURLE_FTP_COULDNT_USE_REST;

	//The server does not support or accept range requests.
	const E_RANGE_ERROR = CURLE_RANGE_ERROR;

	//This is an odd error that mainly occurs due to internal confusion.
	const E_HTTP_POST_ERROR = CURLE_HTTP_POST_ERROR;

	// A problem occurred somewhere in the SSL/TLS handshake. You really want the error buffer and read the message there as it pinpoints the problem slightly more. Could be certificates (file formats, paths, permissions), passwords, and others.
	const E_SSL_CONNECT_ERROR = CURLE_SSL_CONNECT_ERROR;

	//The download could not be resumed because the specified offset was out of the file boundary.
	const E_BAD_DOWNLOAD_RESUME = CURLE_BAD_DOWNLOAD_RESUME;

	//A file given with FILE = // couldn't be opened. Most likely because the file path doesn't identify an existing file. Did you check file permissions?
	const E_FILE_COULDNT_READ_FILE = CURLE_FILE_COULDNT_READ_FILE;

	//LDAP cannot bind. LDAP bind operation failed.
	const E_LDAP_CANNOT_BIND = CURLE_LDAP_CANNOT_BIND;

	//LDAP search failed.
	const E_LDAP_SEARCH_FAILED = CURLE_LDAP_SEARCH_FAILED;

	// Function not found. A required zlib function was not found.
	const E_FUNCTION_NOT_FOUND = CURLE_FUNCTION_NOT_FOUND;

	// Aborted by callback. A callback returned "abort" to libcurl.
	const E_ABORTED_BY_CALLBACK = CURLE_ABORTED_BY_CALLBACK;

	// Internal error. A function was called with a bad parameter.
	const E_BAD_FUNCTION_ARGUMENT = CURLE_BAD_FUNCTION_ARGUMENT;

	//Interface error. A specified outgoing interface could not be used. Set which interface to use for outgoing connections' source IP address with CURLOPT_INTERFACE.
	const E_INTERFACE_FAILED = CURLE_INTERFACE_FAILED;

	//Too many redirects. When following redirects, libcurl hit the maximum amount. Set your limit with CURLOPT_MAXREDIRS.
	const E_TOO_MANY_REDIRECTS = CURLE_TOO_MANY_REDIRECTS;

	//An option passed to libcurl is not recognized/known. Refer to the appropriate documentation. This is most likely a problem in the program that uses libcurl. The error buffer might contain more specific information about which exact option it concerns.
	const E_UNKNOWN_OPTION = CURLE_UNKNOWN_OPTION;

	//A telnet option string was Illegally formatted.
	const E_TELNET_OPTION_SYNTAX = CURLE_TELNET_OPTION_SYNTAX;

	//The remote server's SSL certificate or SSH md5 fingerprint was deemed not OK.
	const E_PEER_FAILED_VERIFICATION = CURLE_PEER_FAILED_VERIFICATION;

	//Nothing was returned from the server, and under the circumstances, getting nothing is considered an error.
	const E_GOT_NOTHING = CURLE_GOT_NOTHING;

	//The specified crypto engine wasn't found.
	const E_SSL_ENGINE_NOTFOUND = CURLE_SSL_ENGINE_NOTFOUND;

	//Failed setting the selected SSL crypto engine as default!
	const E_SSL_ENGINE_SETFAILED = CURLE_SSL_ENGINE_SETFAILED;

	//Failed sending network data.
	const E_SEND_ERROR = CURLE_SEND_ERROR;

	//Failure with receiving network data.
	const E_RECV_ERROR = CURLE_RECV_ERROR;

	//problem with the local client certificate.
	const E_SSL_CERTPROBLEM = CURLE_SSL_CERTPROBLEM;

	//Couldn't use specified cipher.
	const E_SSL_CIPHER = CURLE_SSL_CIPHER;

	//Peer certificate cannot be authenticated with known CA certificates.
	const E_SSL_CACERT = CURLE_SSL_CACERT;

	//Unrecognized transfer encoding.
	const E_BAD_CONTENT_ENCODING = CURLE_BAD_CONTENT_ENCODING;

	//Invalid LDAP URL.
	const E_LDAP_INVALID_URL = CURLE_LDAP_INVALID_URL;

	//Maximum file size exceeded.
	const E_FILESIZE_EXCEEDED = CURLE_FILESIZE_EXCEEDED;

	//Requested FTP SSL level failed.
	const E_USE_SSL_FAILED = CURLE_USE_SSL_FAILED;

	// When doing a send operation curl had to rewind the data to retransmit, but the rewinding operation failed.
	const E_SEND_FAIL_REWIND = CURLE_SEND_FAIL_REWIND;

	//Initiating the SSL Engine failed.
	const E_SSL_ENGINE_INITFAILED = CURLE_SSL_ENGINE_INITFAILED;

	//The remote server denied curl to login (Added in 7.13.1)
	const E_LOGIN_DENIED = CURLE_LOGIN_DENIED;

	//File not found on TFTP server.
	const E_TFTP_NOTFOUND = CURLE_TFTP_NOTFOUND;

	//Permission problem on TFTP server.
	const E_TFTP_PERM = CURLE_TFTP_PERM;

	//Out of disk space on the server.
	const E_REMOTE_DISK_FULL = CURLE_REMOTE_DISK_FULL;

	//Illegal TFTP operation.
	const E_TFTP_ILLEGAL = CURLE_TFTP_ILLEGAL;

	//Unknown TFTP transfer ID.
	const E_TFTP_UNKNOWNID = CURLE_TFTP_UNKNOWNID;

	//File already exists and will not be overwritten.
	const E_REMOTE_FILE_EXISTS = CURLE_REMOTE_FILE_EXISTS;

	//This error should never be returned by a properly functioning TFTP server.
	const E_TFTP_NOSUCHUSER = CURLE_TFTP_NOSUCHUSER;

	//Character conversion failed.
	const E_CONV_FAILED = CURLE_CONV_FAILED;

	//Caller must register conversion callbacks.
	const E_CONV_REQD = CURLE_CONV_REQD;

	//Problem with reading the SSL CA cert (path? access rights?)
	const E_SSL_CACERT_BADFILE = CURLE_SSL_CACERT_BADFILE;

	//The resource referenced in the URL does not exist.
	const E_REMOTE_FILE_NOT_FOUND = CURLE_REMOTE_FILE_NOT_FOUND;

	//An unspecified error occurred during the SSH session.
	const E_SSH = CURLE_SSH;

	//Failed to shut down the SSL connection.
	const E_SSL_SHUTDOWN_FAILED = CURLE_SSL_SHUTDOWN_FAILED;

	//Socket is not ready for send/recv wait till it's ready and try again. This return code is only returned from curl_easy_recv(3) and curl_easy_send(3) (Added in 7.18.2)
	const E_AGAIN = CURLE_AGAIN;

	//Failed to load CRL file (Added in 7.19.0)
	const E_SSL_CRL_BADFILE = CURLE_SSL_CRL_BADFILE;

	//Issuer check failed (Added in 7.19.0)
	const E_SSL_ISSUER_ERROR = CURLE_SSL_ISSUER_ERROR;

	//The FTP server does not understand the PRET command at all or does not support the given argument. Be careful when using CURLOPT_CUSTOMREQUEST, a custom LIST command will be sent with PRET CMD before PASV as well. (Added in 7.20.0)
	const E_FTP_PRET_FAILED = CURLE_FTP_PRET_FAILED;

	//Mismatch of RTSP CSeq numbers.
	const E_RTSP_CSEQ_ERROR = CURLE_RTSP_CSEQ_ERROR;

	//Mismatch of RTSP Session Identifiers.
	const E_RTSP_SESSION_ERROR = CURLE_RTSP_SESSION_ERROR;

	//Unable to parse FTP file list (during FTP wildcard downloading).
	const E_FTP_BAD_FILE_LIST = CURLE_FTP_BAD_FILE_LIST;

	//Chunk callback reported error.
	const E_CHUNK_FAILED = CURLE_CHUNK_FAILED;

	//(For internal use only, will never be returned by libcurl) No connection available, the session will be queued. (added in 7.30.0)
	const E_NO_CONNECTION_AVAILABLE = CURLE_NO_CONNECTION_AVAILABLE;

	//These error codes will never be returned. They were used in an old libcurl version and are currently unused.
	const E_OBSOLETE = CURLE_OBSOLETE;


	final static public function getCurlErrorNumberByText($errorText) {
		//ф-ия направленая на исправление ситцуации когда текст ошибки есть, а кода нет.
		// кривой, мать его cURL
		$errorCode = 0;
		if( strpos($errorText, 'timed out') !== false
			&& strpos($errorText, 'millisec') !== false
		) {
			$errorCode = CurlError::E_OPERATION_TIMEDOUT;
		}
		elseif( strpos($errorText, 'Could not resolve host') !== false ) {
			$errorCode = CurlError::E_COULDNT_RESOLVE_HOST;
		}
		elseif( strpos($errorText, 'Cannot resume') !== false
				&& strpos($errorText, 'support byte ranges') !== false
		) {
			$errorCode = CurlError::E_RANGE_ERROR;
		}
		else {
			// если не нашел, все равно надо давать код исключения
			$errorCode = CurlError::E_UNKNOWN_ERROR_CODE;
		}
		return $errorCode;
	}
}