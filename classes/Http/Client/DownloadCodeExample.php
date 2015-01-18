<?php

/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

class OBX_ImportTools
{

	static public $CONFIG = array();

	static protected $currentStatus;
	static protected $currentProgress;
	static protected $debugLevel = 0;
	static public function SetCurrentStatus($str) {
		self::$currentStatus .= $str."\n";
	}
	static public function GetCurrentStatus() {
		return self::$currentStatus;
	}

	static public function SetDebugLevel($debugLevel) {
		$debugLevel = intval($debugLevel);
		if($debugLevel >= 0 || $debugLevel <= 9) {
			self::$debugLevel = $debugLevel;
		}
	}
	static protected function CheckDebugLevel($debugLevel) {
		$debugLevel = intval($debugLevel);
		if($debugLevel <= self::$debugLevel) {
			return true;
		}
		return false;
	}
	static public function GetDebugLevel() {

	}

	static protected function img($name)
	{
		if (file_exists($_SERVER['DOCUMENT_ROOT'].'/images/'.$name))
			return '/images/'.$name;
		return 'http://www.1c-bitrix.ru/images/bitrix_setup/'.$name;
	}
	static public function SetCurrentProgress($cur,$total=0,$red=true)
	{
		if (!$total)
		{
			$total=100;
			$cur=0;
		}
		$val = intval($cur/$total*100);
		if ($val > 99)
			$val = 99;

		self::$currentProgress = '
		<div align=center style="padding:10px;font-size:18px">'.$val.'%</div>
		<table width=100% cellspacing=0 cellpadding=0 border=0 style="border:1px solid #D8D8D8">
		<tr>
			<td style="width:'.$val.'%;height:13px" bgcolor="'.($red?'#FF5647':'#54B4FF').'" background="'.self::img(($red?'red':'blue').'_progress.gif').'"></td>
			<td style="width:'.(100-$val).'%"></td>
		</tr>
		</table>';
	}
	static public function GetCurrentProgress() {
		return self::$currentProgress;
	}

	// Bitrix Standart function for installing and restoring (bitrixsetup.php)
	static public function LoadFile($strRequestedUrl, $strFilename, $iTimeOut)
	{
		global $proxyAddr, $proxyPort, $proxyUserName, $proxyPassword, $strUserAgent, $strRequestedSize;

		$iTimeOut = IntVal($iTimeOut);
		if ($iTimeOut>0)
			$start_time = getmicrotime();

		$strRealUrl = $strRequestedUrl;
		$iStartSize = 0;
		$iRealSize = 0;

		$bCanContinueDownload = False;

		// ИНИЦИАЛИЗИРУЕМ, ЕСЛИ ДОКАЧКА
		$strRealUrl_tmp = "";
		$iRealSize_tmp = 0;
		if (file_exists($strFilename.".tmp") && file_exists($strFilename.".log") && filesize($strFilename.".log")>0)
		{
			$fh = fopen($strFilename.".log", "rb");
			$file_contents_tmp = fread($fh, filesize($strFilename.".log"));
			fclose($fh);

			list($strRealUrl_tmp, $iRealSize_tmp) = split("\n", $file_contents_tmp);
			$strRealUrl_tmp = Trim($strRealUrl_tmp);
			$iRealSize_tmp = Trim($iRealSize_tmp);
		}
		if ($iRealSize_tmp<=0 || strlen($strRealUrl_tmp)<=0)
		{
			$strRealUrl_tmp = "";
			$iRealSize_tmp = 0;

			if (file_exists($strFilename.".tmp"))
				@unlink($strFilename.".tmp");

			if (file_exists($strFilename.".log"))
				@unlink($strFilename.".log");
		}
		else
		{
			$strRealUrl = $strRealUrl_tmp;
			$iRealSize = $iRealSize_tmp;
			$iStartSize = filesize($strFilename.".tmp");
		}
		// КОНЕЦ: ИНИЦИАЛИЗИРУЕМ, ЕСЛИ ДОКАЧКА

		if(self::CheckDebugLevel(2)) self::SetCurrentStatus(GetMessage("LOADER_LOAD_QUERY_SERVER"));

		// ИЩЕМ ФАЙЛ И ЗАПРАШИВАЕМ ИНФО
		do
		{
			if(self::CheckDebugLevel(2)) self::SetCurrentStatus(str_replace("#DISTR#", $strRealUrl, GetMessage("LOADER_LOAD_QUERY_DISTR")));

			$lasturl = $strRealUrl;
			$redirection = "";

			$parsedurl = parse_url($strRealUrl);
			$useproxy = (($proxyAddr != "") && ($proxyPort != ""));

			if (!$useproxy)
			{
				$host = $parsedurl["host"];
				$port = $parsedurl["port"];
				$hostname = $host;
			}
			else
			{
				$host = $proxyAddr;
				$port = $proxyPort;
				$hostname = $parsedurl["host"];
			}

			$port = $port ? $port : "80";

			if(self::CheckDebugLevel(2)) self::SetCurrentStatus(str_replace("#HOST#", $host, GetMessage("LOADER_LOAD_CONN2HOST")));
			$sockethandle = fsockopen($host, $port, $error_id, $error_msg, 30);
			if (!$sockethandle)
			{
				if(self::CheckDebugLevel(2)) self::SetCurrentStatus(str_replace("#HOST#", $host, GetMessage("LOADER_LOAD_NO_CONN2HOST"))." [".$error_id."] ".$error_msg);
				return false;
			}
			else
			{
				if (!$parsedurl["path"])
					$parsedurl["path"] = "/";

				if(self::CheckDebugLevel(2)) self::SetCurrentStatus(GetMessage("LOADER_LOAD_QUERY_FILE"));
				$request = "";
				if (!$useproxy)
				{
					$request .= "HEAD ".$parsedurl["path"].($parsedurl["query"] ? '?'.$parsedurl["query"] : '')." HTTP/1.0\r\n";
					$request .= "Host: $hostname\r\n";
				}
				else
				{
					$request .= "HEAD ".$strRealUrl." HTTP/1.0\r\n";
					$request .= "Host: $hostname\r\n";
					if ($proxyUserName)
						$request .= "Proxy-Authorization: Basic ".base64_encode($proxyUserName.":".$proxyPassword)."\r\n";
				}

				if ($strUserAgent != "")
					$request .= "User-Agent: $strUserAgent\r\n";

				$request .= "\r\n";

				fwrite($sockethandle, $request);

				$result = "";
				if(self::CheckDebugLevel(2)) self::SetCurrentStatus(GetMessage("LOADER_LOAD_WAIT"));

				$replyheader = "";
				while (($result = fgets($sockethandle, 4024)) && $result!="\r\n")
				{
					$replyheader .= $result;
				}
				fclose($sockethandle);

				$ar_replyheader = split("\r\n", $replyheader);

				$replyproto = "";
				$replyversion = "";
				$replycode = 0;
				$replymsg = "";
				if (ereg("([A-Z]{4})/([0-9.]{3}) ([0-9]{3})", $ar_replyheader[0], $regs))
				{
					$replyproto = $regs[1];
					$replyversion = $regs[2];
					$replycode = IntVal($regs[3]);
					$replymsg = substr($ar_replyheader[0], strpos($ar_replyheader[0], $replycode) + strlen($replycode) + 1, strlen($ar_replyheader[0]) - strpos($ar_replyheader[0], $replycode) + 1);
				}

				if ($replycode!=200 && $replycode!=302)
				{
					if ($replycode==403)
						self::SetCurrentStatus(GetMessage("LOADER_LOAD_SERVER_ANSWER1"));
					else
						self::SetCurrentStatus(str_replace("#ANS#", $replycode." - ".$replymsg, GetMessage("LOADER_LOAD_SERVER_ANSWER")).'<br>'.htmlspecialchars($strRequestedUrl));
					return false;
				}

				$strLocationUrl = "";
				$iNewRealSize = 0;
				$strAcceptRanges = "";
				for ($i = 1; $i < count($ar_replyheader); $i++)
				{
					if (strpos($ar_replyheader[$i], "Location") !== false)
						$strLocationUrl = trim(substr($ar_replyheader[$i], strpos($ar_replyheader[$i], ":") + 1, strlen($ar_replyheader[$i]) - strpos($ar_replyheader[$i], ":") + 1));
					elseif (strpos($ar_replyheader[$i], "Content-Length") !== false)
						$iNewRealSize = IntVal(Trim(substr($ar_replyheader[$i], strpos($ar_replyheader[$i], ":") + 1, strlen($ar_replyheader[$i]) - strpos($ar_replyheader[$i], ":") + 1)));
					elseif (strpos($ar_replyheader[$i], "Accept-Ranges") !== false)
						$strAcceptRanges = Trim(substr($ar_replyheader[$i], strpos($ar_replyheader[$i], ":") + 1, strlen($ar_replyheader[$i]) - strpos($ar_replyheader[$i], ":") + 1));
				}

				if (strlen($strLocationUrl)>0)
				{
					$redirection = $strLocationUrl;
					$redirected = true;
					if ((strpos($redirection, "http://")===false))
						$strRealUrl = dirname($lasturl)."/".$redirection;
					else
						$strRealUrl = $redirection;
				}

				if (strlen($strLocationUrl)<=0)
					break;
			}
		}
		while (true);
		// КОНЕЦ: ИЩЕМ ФАЙЛ И ЗАПРАШИВАЕМ ИНФО

		$bCanContinueDownload = ($strAcceptRanges == "bytes");

		/*
			// ЕСЛИ НЕЛЬЗЯ ДОКАЧИВАТЬ
			if (!$bCanContinueDownload
				|| ($iRealSize>0 && $iNewRealSize != $iRealSize))
			{
				if(self::CheckDebugLevel(2)) self::SetCurrentStatus(GetMessage("LOADER_LOAD_NEED_RELOAD"));
			//	$iStartSize = 0;
				die(GetMessage("LOADER_LOAD_NEED_RELOAD"));
			}
			// КОНЕЦ: ЕСЛИ НЕЛЬЗЯ ДОКАЧИВАТЬ
		*/

		// ЕСЛИ МОЖНО ДОКАЧИВАТЬ
		if ($bCanContinueDownload)
		{
			$fh = fopen($strFilename.".log", "wb");
			if (!$fh)
			{
				self::SetCurrentStatus(str_replace("#FILE#", $strFilename.".log", GetMessage("LOADER_LOAD_NO_WRITE2FILE")));
				return false;
			}
			fwrite($fh, $strRealUrl."\n");
			fwrite($fh, $iNewRealSize."\n");
			fclose($fh);
		}
		// КОНЕЦ: ЕСЛИ МОЖНО ДОКАЧИВАТЬ

		if(self::CheckDebugLevel(2)) self::SetCurrentStatus(str_replace("#DISTR#", $strRealUrl, GetMessage("LOADER_LOAD_LOAD_DISTR")));
		$strRequestedSize = $iNewRealSize;

		// КАЧАЕМ ФАЙЛ
		$parsedurl = parse_url($strRealUrl);
		$useproxy = (($proxyAddr != "") && ($proxyPort != ""));

		if (!$useproxy)
		{
			$host = $parsedurl["host"];
			$port = $parsedurl["port"];
			$hostname = $host;
		}
		else
		{
			$host = $proxyAddr;
			$port = $proxyPort;
			$hostname = $parsedurl["host"];
		}

		$port = $port ? $port : "80";

		self::SetCurrentStatus(str_replace("#HOST#", $host, GetMessage("LOADER_LOAD_CONN2HOST")));
		$sockethandle = fsockopen($host, $port, $error_id, $error_msg, 30);
		if (!$sockethandle)
		{
			self::SetCurrentStatus(str_replace("#HOST#", $host, GetMessage("LOADER_LOAD_NO_CONN2HOST"))." [".$error_id."] ".$error_msg);
			return false;
		}
		else
		{
			if (!$parsedurl["path"])
				$parsedurl["path"] = "/";

			self::SetCurrentStatus(GetMessage("LOADER_LOAD_QUERY_FILE"));

			$request = "";
			if (!$useproxy)
			{
				$request .= "GET ".$parsedurl["path"].($parsedurl["query"] ? '?'.$parsedurl["query"] : '')." HTTP/1.0\r\n";
				$request .= "Host: $hostname\r\n";
			}
			else
			{
				$request .= "GET ".$strRealUrl." HTTP/1.0\r\n";
				$request .= "Host: $hostname\r\n";
				if ($proxyUserName)
					$request .= "Proxy-Authorization: Basic ".base64_encode($proxyUserName.":".$proxyPassword)."\r\n";
			}

			if ($strUserAgent != "")
				$request .= "User-Agent: $strUserAgent\r\n";

			if ($bCanContinueDownload && $iStartSize>0)
				$request .= "Range: bytes=".$iStartSize."-\r\n";

			$request .= "\r\n";

			fwrite($sockethandle, $request);

			$result = "";
			self::SetCurrentStatus(GetMessage("LOADER_LOAD_WAIT"));

			$replyheader = "";
			while (($result = fgets($sockethandle, 4096)) && $result!="\r\n")
				$replyheader .= $result;

			$ar_replyheader = split("\r\n", $replyheader);

			$replyproto = "";
			$replyversion = "";
			$replycode = 0;
			$replymsg = "";
			if (ereg("([A-Z]{4})/([0-9.]{3}) ([0-9]{3})", $ar_replyheader[0], $regs))
			{
				$replyproto = $regs[1];
				$replyversion = $regs[2];
				$replycode = IntVal($regs[3]);
				$replymsg = substr($ar_replyheader[0], strpos($ar_replyheader[0], $replycode) + strlen($replycode) + 1, strlen($ar_replyheader[0]) - strpos($ar_replyheader[0], $replycode) + 1);
			}

			if ($replycode!=200 && $replycode!=302 && $replycode!=206)
			{
				self::SetCurrentStatus(str_replace("#ANS#", $replycode." - ".$replymsg, GetMessage("LOADER_LOAD_SERVER_ANSWER")));
				return false;
			}

			$strContentRange = "";
			$iContentLength = 0;
			$strAcceptRanges = "";
			for ($i = 1; $i < count($ar_replyheader); $i++)
			{
				if (strpos($ar_replyheader[$i], "Content-Range") !== false)
					$strContentRange = trim(substr($ar_replyheader[$i], strpos($ar_replyheader[$i], ":") + 1, strlen($ar_replyheader[$i]) - strpos($ar_replyheader[$i], ":") + 1));
				elseif (strpos($ar_replyheader[$i], "Content-Length") !== false)
					$iContentLength = doubleval(Trim(substr($ar_replyheader[$i], strpos($ar_replyheader[$i], ":") + 1, strlen($ar_replyheader[$i]) - strpos($ar_replyheader[$i], ":") + 1)));
				elseif (strpos($ar_replyheader[$i], "Accept-Ranges") !== false)
					$strAcceptRanges = Trim(substr($ar_replyheader[$i], strpos($ar_replyheader[$i], ":") + 1, strlen($ar_replyheader[$i]) - strpos($ar_replyheader[$i], ":") + 1));
			}

			$bReloadFile = True;
			if (strlen($strContentRange)>0)
			{
				if (eregi(" *bytes +([0-9]*) *- *([0-9]*) */ *([0-9]*)", $strContentRange, $regs))
				{
					$iStartBytes_tmp = doubleval($regs[1]);
					$iEndBytes_tmp = doubleval($regs[2]);
					$iSizeBytes_tmp = doubleval($regs[3]);

					if ($iStartBytes_tmp==$iStartSize
						&& $iEndBytes_tmp==($iNewRealSize-1)
						&& $iSizeBytes_tmp==$iNewRealSize)
					{
						$bReloadFile = False;
					}
				}
			}

			if ($bReloadFile)
			{
				@unlink($strFilename.".tmp");
				$iStartSize = 0;
			}

			if (($iContentLength+$iStartSize)!=$iNewRealSize)
			{
				self::SetCurrentStatus(GetMessage("LOADER_LOAD_ERR_SIZE"));
				return false;
			}

			$fh = fopen($strFilename.".tmp", "ab");
			if (!$fh)
			{
				self::SetCurrentStatus(str_replace("#FILE#", $strFilename.".tmp", GetMessage("LOADER_LOAD_CANT_OPEN_WRITE")));
				return false;
			}

			$bFinished = True;
			$downloadsize = (double) $iStartSize;
			self::SetCurrentStatus(GetMessage("LOADER_LOAD_LOADING"));
			while (!feof($sockethandle))
			{
				if ($iTimeOut>0 && (getmicrotime()-$start_time)>$iTimeOut)
				{
					$bFinished = False;
					break;
				}

				$result = fread($sockethandle, 256 * 1024);
				$downloadsize += strlen($result);
				if ($result=="")
					break;

				fwrite($fh, $result);
			}
			self::SetCurrentProgress($downloadsize,$iNewRealSize);

			fclose($fh);
			fclose($sockethandle);

			if ($bFinished)
			{
				@unlink($strFilename);
				if (!@rename($strFilename.".tmp", $strFilename))
				{
					self::SetCurrentStatus(str_replace("#FILE2#", $strFilename, str_replace("#FILE1#", $strFilename.".tmp", GetMessage("LOADER_LOAD_ERR_RENAME"))));
					return false;
				}
				@unlink($strFilename.".tmp");
			}
			else
				return 2;

			self::SetCurrentStatus(str_replace("#SIZE#", $downloadsize, str_replace("#FILE#", $strFilename, GetMessage("LOADER_LOAD_FILE_SAVED"))));
			@unlink($strFilename.".log");
			return 1;
		}
		// КОНЕЦ: КАЧАЕМ ФАЙЛ
	}// КОНЕЦ: КАЧАЕМ ФАЙЛ


	static public function Unpack()
	{

	}
}