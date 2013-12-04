<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/
use OBX\Core\Exceptions\Curl\CurlError as _;
$MESS[_::LANG_PREFIX._::E_UNSUPPORTED_PROTOCOL] = 'The URL you passed to libcurl used a protocol that this libcurl does not support. The support might be a compile-time option that you didn\'t use, it can be a misspelled protocol string or just a protocol libcurl has no code for';
$MESS[_::LANG_PREFIX._::E_FAILED_INIT] = 'Very early initialization code failed. This is likely to be an internal error or problem, or a resource problem where something fundamental couldn\'t get done at init time.';
$MESS[_::LANG_PREFIX._::E_URL_MALFORMAT] = 'The URL was not properly formatted.';
$MESS[_::LANG_PREFIX._::E_NOT_BUILT_IN] = 'A requested feature, protocol or option was not found built-in in this libcurl due to a build-time decision. This means that a feature or option was not enabled or explicitly disabled when libcurl was built and in order to get it to function you have to get a rebuilt libcurl.';
$MESS[_::LANG_PREFIX._::E_COULDNT_RESOLVE_PROXY] = 'Couldn\'t resolve proxy. The given proxy host could not be resolved.';
$MESS[_::LANG_PREFIX._::E_COULDNT_RESOLVE_HOST] = 'Couldn\'t resolve host. The given remote host was not resolved.';
$MESS[_::LANG_PREFIX._::E_COULDNT_CONNECT] = 'Failed to connect() to host or proxy.';
$MESS[_::LANG_PREFIX._::E_FTP_WEIRD_SERVER_REPLY] = 'After connecting to a FTP server, libcurl expects to get a certain reply back. This error code implies that it got a strange or bad reply. The given remote server is probably not an OK FTP server.';
$MESS[_::LANG_PREFIX._::E_REMOTE_ACCESS_DENIED] = 'We were denied access to the resource given in the URL. For FTP, this occurs while trying to change to the remote directory.';
$MESS[_::LANG_PREFIX._::E_FTP_ACCEPT_FAILED] = 'While waiting for the server to connect back when an active FTP session is used, an error code was sent over the control connection or similar.';
$MESS[_::LANG_PREFIX._::E_FTP_WEIRD_PASS_REPLY] = 'After having sent the FTP password to the server, libcurl expects a proper reply. This error code indicates that an unexpected code was returned.';
$MESS[_::LANG_PREFIX._::E_FTP_ACCEPT_TIMEOUT] = 'During an active FTP session while waiting for the server to connect, the CURLOPT_ACCEPTTIMOUT_MS (or the internal default) timeout expired.';
$MESS[_::LANG_PREFIX._::E_FTP_WEIRD_PASV_REPLY] = 'libcurl failed to get a sensible result back from the server as a response to either a PASV or a EPSV command. The server is flawed.';
$MESS[_::LANG_PREFIX._::E_FTP_WEIRD_227_FORMAT] = 'FTP servers return a 227-line as a response to a PASV command. If libcurl fails to parse that line, this return code is passed back.';
$MESS[_::LANG_PREFIX._::E_FTP_CANT_GET_HOST] = 'An internal failure to lookup the host used for the new connection.';
$MESS[_::LANG_PREFIX._::E_FTP_COULDNT_SET_TYPE] = 'Received an error when trying to set the transfer mode to binary or ASCII.';
$MESS[_::LANG_PREFIX._::E_PARTIAL_FILE] = 'A file transfer was shorter or larger than expected. This happens when the server first reports an expected transfer size, and then delivers data that doesn\'t match the previously given size.';
$MESS[_::LANG_PREFIX._::E_FTP_COULDNT_RETR_FILE] = 'This was either a weird reply to a \'RETR\' command or a zero byte transfer complete.';
$MESS[_::LANG_PREFIX._::E_QUOTE_ERROR] = 'When sending custom "QUOTE" commands to the remote server, one of the commands returned an error code that was 400 or higher (for FTP) or otherwise indicated unsuccessful completion of the command.';
$MESS[_::LANG_PREFIX._::E_HTTP_RETURNED_ERROR] = 'This is returned if CURLOPT_FAILONERROR is set TRUE and the HTTP server returns an error code that is >= 400.';
$MESS[_::LANG_PREFIX._::E_WRITE_ERROR] = 'An error occurred when writing received data to a local file, or an error was returned to libcurl from a write callback.';
$MESS[_::LANG_PREFIX._::E_UPLOAD_FAILED] = 'Failed starting the upload. For FTP, the server typically denied the STOR command. The error buffer usually contains the server\'s explanation for this.';
$MESS[_::LANG_PREFIX._::E_READ_ERROR] = 'There was a problem reading a local file or an error returned by the read callback.';
$MESS[_::LANG_PREFIX._::E_OUT_OF_MEMORY] = 'A memory allocation request failed. This is serious badness and things are severely screwed up if this ever occurs.';
$MESS[_::LANG_PREFIX._::E_OPERATION_TIMEDOUT] = 'Operation timeout. The specified time-out period was reached according to the conditions.';
$MESS[_::LANG_PREFIX._::E_FTP_PORT_FAILED] = 'The FTP PORT command returned error. This mostly happens when you haven\'t specified a good enough address for libcurl to use. See CURLOPT_FTPPORT.';
$MESS[_::LANG_PREFIX._::E_FTP_COULDNT_USE_REST] = 'The FTP REST command returned error. This should never happen if the server is sane.';
$MESS[_::LANG_PREFIX._::E_RANGE_ERROR] = 'The server does not support or accept range requests.';
$MESS[_::LANG_PREFIX._::E_HTTP_POST_ERROR] = 'This is an odd error that mainly occurs due to internal confusion.';
$MESS[_::LANG_PREFIX._::E_SSL_CONNECT_ERROR] = 'A problem occurred somewhere in the SSL/TLS handshake. You really want the error buffer and read the message there as it pinpoints the problem slightly more. Could be certificates (file formats, paths, permissions), passwords, and others.';
$MESS[_::LANG_PREFIX._::E_BAD_DOWNLOAD_RESUME] = 'The download could not be resumed because the specified offset was out of the file boundary.';
$MESS[_::LANG_PREFIX._::E_FILE_COULDNT_READ_FILE] = 'A file given with FILE = // Couldn\'t be opened. Most likely because the file path doesn\'t identify an existing file. Did you check file permissions?';
$MESS[_::LANG_PREFIX._::E_LDAP_CANNOT_BIND] = 'LDAP cannot bind. LDAP bind operation failed.';
$MESS[_::LANG_PREFIX._::E_LDAP_SEARCH_FAILED] = 'LDAP search failed.';
$MESS[_::LANG_PREFIX._::E_FUNCTION_NOT_FOUND] = 'Function not found. A required zlib function was not found.';
$MESS[_::LANG_PREFIX._::E_ABORTED_BY_CALLBACK] = 'Aborted by callback. A callback returned "abort" to libcurl.';
$MESS[_::LANG_PREFIX._::E_BAD_FUNCTION_ARGUMENT] = 'Internal error. A function was called with a bad parameter.';
$MESS[_::LANG_PREFIX._::E_INTERFACE_FAILED] = 'Interface error. A specified outgoing interface could not be used. Set which interface to use for outgoing connections\' source IP address with CURLOPT_INTERFACE.';
$MESS[_::LANG_PREFIX._::E_TOO_MANY_REDIRECTS] = 'Too many redirects. When following redirects, libcurl hit the maximum amount. Set your limit with CURLOPT_MAXREDIRS.';
$MESS[_::LANG_PREFIX._::E_UNKNOWN_OPTION] = 'An option passed to libcurl is not recognized/known. Refer to the appropriate documentation. This is most likely a problem in the program that uses libcurl. The error buffer might contain more specific information about which exact option it concerns.';
$MESS[_::LANG_PREFIX._::E_TELNET_OPTION_SYNTAX] = 'A telnet option string was Illegally formatted.';
$MESS[_::LANG_PREFIX._::E_PEER_FAILED_VERIFICATION] = 'The remote server\'s SSL certificate or SSH md5 fingerprint was deemed not OK.';
$MESS[_::LANG_PREFIX._::E_GOT_NOTHING] = 'Nothing was returned from the server, and under the circumstances, getting nothing is considered an error.';
$MESS[_::LANG_PREFIX._::E_SSL_ENGINE_NOTFOUND] = 'The specified crypto engine wasn\'t found.';
$MESS[_::LANG_PREFIX._::E_SSL_ENGINE_SETFAILED] = 'Failed setting the selected SSL crypto engine as default!';
$MESS[_::LANG_PREFIX._::E_SEND_ERROR] = 'Failed sending network data.';
$MESS[_::LANG_PREFIX._::E_RECV_ERROR] = 'Failure with receiving network data.';
$MESS[_::LANG_PREFIX._::E_SSL_CERTPROBLEM] = 'problem with the local client certificate.';
$MESS[_::LANG_PREFIX._::E_SSL_CIPHER] = 'Couldn\'t use specified cipher.';
$MESS[_::LANG_PREFIX._::E_SSL_CACERT] = 'Peer certificate cannot be authenticated with known CA certificates.';
$MESS[_::LANG_PREFIX._::E_BAD_CONTENT_ENCODING] = 'Unrecognized transfer encoding.';
$MESS[_::LANG_PREFIX._::E_LDAP_INVALID_URL] = 'Invalid LDAP URL.';
$MESS[_::LANG_PREFIX._::E_FILESIZE_EXCEEDED] = 'Maximum file size exceeded.';
$MESS[_::LANG_PREFIX._::E_USE_SSL_FAILED] = 'Requested FTP SSL level failed.';
$MESS[_::LANG_PREFIX._::E_SEND_FAIL_REWIND] = 'When doing a send operation curl had to rewind the data to retransmit, but the rewinding operation failed.';
$MESS[_::LANG_PREFIX._::E_SSL_ENGINE_INITFAILED] = 'Initiating the SSL Engine failed.';
$MESS[_::LANG_PREFIX._::E_LOGIN_DENIED] = 'The remote server denied curl to login (Added in 7.13.1)';
$MESS[_::LANG_PREFIX._::E_TFTP_NOTFOUND] = 'File not found on TFTP server.';
$MESS[_::LANG_PREFIX._::E_TFTP_PERM] = 'Permission problem on TFTP server.';
$MESS[_::LANG_PREFIX._::E_REMOTE_DISK_FULL] = 'Out of disk space on the server.';
$MESS[_::LANG_PREFIX._::E_TFTP_ILLEGAL] = 'Illegal TFTP operation.';
$MESS[_::LANG_PREFIX._::E_TFTP_UNKNOWNID] = 'Unknown TFTP transfer ID.';
$MESS[_::LANG_PREFIX._::E_REMOTE_FILE_EXISTS] = 'File already exists and will not be overwritten.';
$MESS[_::LANG_PREFIX._::E_TFTP_NOSUCHUSER] = 'This error should never be returned by a properly functioning TFTP server.';
$MESS[_::LANG_PREFIX._::E_CONV_FAILED] = 'Character conversion failed.';
$MESS[_::LANG_PREFIX._::E_CONV_REQD] = 'Caller must register conversion callbacks.';
$MESS[_::LANG_PREFIX._::E_SSL_CACERT_BADFILE] = 'Problem with reading the SSL CA cert (path? access rights?)';
$MESS[_::LANG_PREFIX._::E_REMOTE_FILE_NOT_FOUND] = 'The resource referenced in the URL does not exist.';
$MESS[_::LANG_PREFIX._::E_SSH] = 'An unspecified error occurred during the SSH session.';
$MESS[_::LANG_PREFIX._::E_SSL_SHUTDOWN_FAILED] = 'Failed to shut down the SSL connection.';
$MESS[_::LANG_PREFIX._::E_AGAIN] = 'Socket is not ready for send/recv wait till it\'s ready and try again. This return code is only returned from curl_easy_recv(3) and curl_easy_send(3) (Added in 7.18.2)';
$MESS[_::LANG_PREFIX._::E_SSL_CRL_BADFILE] = 'Failed to load CRL file (Added in 7.19.0)';
$MESS[_::LANG_PREFIX._::E_SSL_ISSUER_ERROR] = 'Issuer check failed (Added in 7.19.0)';
$MESS[_::LANG_PREFIX._::E_FTP_PRET_FAILED] = 'The FTP server does not understand the PRET command at all or does not support the given argument. Be careful when using CURLOPT_CUSTOMREQUEST, a custom LIST command will be sent with PRET CMD before PASV as well. (Added in 7.20.0)';
$MESS[_::LANG_PREFIX._::E_RTSP_CSEQ_ERROR] = 'Mismatch of RTSP CSeq numbers.';
$MESS[_::LANG_PREFIX._::E_RTSP_SESSION_ERROR] = 'Mismatch of RTSP Session Identifiers.';
$MESS[_::LANG_PREFIX._::E_FTP_BAD_FILE_LIST] = 'Unable to parse FTP file list (during FTP wildcard downloading).';
$MESS[_::LANG_PREFIX._::E_CHUNK_FAILED] = 'Chunk callback reported error.';
$MESS[_::LANG_PREFIX._::E_NO_CONNECTION_AVAILABLE] = '(For internal use only, will never be returned by libcurl) No connection available, the session will be queued. (added in 7.30.0)';
$MESS[_::LANG_PREFIX._::E_OBSOLETE] = 'These error codes will never be returned. They were used in an old libcurl version and are currently unused.';