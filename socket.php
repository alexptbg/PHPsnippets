<?php
defined('start') or die('Direct access not allowed.');
class ClientSocket{
	private $hnd;
	private $host;
	private $ip;
	private $port;
	private $type;
	private $family;
	private $protocol;
	private $bConnected;
	private $sBuffer;
	private $iReadTimeOut = 2;//2
	private $iWriteTimeOut = 2;//2
	public $bShowErros	= false;//false
	public $bExceptions = true;
	public function error($msg=null){
		if(!$this->bShowErros && !$this->bExceptions) return;
		$errCode = socket_last_error($this->hnd);
		if($errCode!=0){
			if($errCode==104)
				$this->bConnected = false;
			$errMsg = socket_strerror($errCode);
			if($this->bExceptions){
				throw new Exception("Socket error. Code: $errCode - Message: $errMsg\n");
			} else {
				trigger_error("Socket Error. Code: $errCode - Message: $errMsg");
			}
			socket_clear_error($this->hnd);
		} elseif (strlen($msg)){
			if($this->bExceptions){
				throw new Exception("Socket error." . $msg);
			}
			else{
				trigger_error("$msg\n",E_USER_ERROR);
			}
		}
	}
	public function __construct($family=AF_INET,$type=SOCK_STREAM ,$protocol=SOL_TCP){
		$this->hnd 		= @socket_create($family,$type,$protocol);
		$this->error();
		$this->family 	= $family;
		$this->typ 		= $type;
		$this->protocol	= $protocol;
		$this->sBuffer 	= false;
		$this->port		= null;
		$this->ip		= null;
		$this->host		= null;
	}
	public function setHost($sHost){
		if(!strlen($sHost)) return;
		$this->host[]	= $sHost;
		$ip				= @gethostbyname($sHost);
		if($ip){
			$this->ip[]	= $ip;
		}else{
			$this->error("Hostname $sHost could not be resolved");
		}
	}
	public function setIp($sIp){
		if(!strlen($sIp)) return;
		if(!ip2long($sIp)){
			$this->error("Invalid IP ADDRESS. IP $sIp");
		}
		$this->ip[]		= $sIp;
		$this->host[]	= @gethostbyaddr($sIp);
	}
	public function setPort($iPort){
		$this->port 	= $iPort;
	}
	public function open($sHost=null,$iPort=null){
		if(strlen($sHost)){
			$this->setHost($sHost);
		}
		if(strlen($iPort)){
			$this->setPort($iPort);
		}
		$i = 0;
		do{
			if(@socket_connect($this->hnd,$this->ip[$i],$this->port)){
				$this->bConnected 	= true;
			}
		}
		while (!$this->bConnected && $i++<count($this->ip));
		if(!$this->bConnected)
			$this->error();
	}
	public function connect($sHost=null,$iPort=null){
		return $this->open($sHost,$iPort);
	}
	public function close(){
		if(!$this->bConnected) return;
		@socket_shutdown($this->hnd,2);
		@socket_close($this->hnd);
	}
	public function disconnect(){
		return $this->close();
	}
	public function send($sBuf,$iTimeOut=null){
		if(!strlen($this->sBuffer) && !strlen($sBuf)) return;
		if(!$this->bConnected){
			$this->error("Socket error. Cannot send data on a closed socket.");
			return;
		}
		$vWrite = array($this->hnd);
		$WriteTimeOut = strlen($iTimeOut) ? $iTimeOut : $this->iWriteTimeOut;
		while(($rr = @socket_select($vRead = null,$vWrite ,$vExcept = null,$WriteTimeOut))===FALSE);
		if($rr==0) return;
		$tmpBuf		= strlen($sBuf) ? $sBuf : $this->sBuffer;
		$iBufLen	= strlen($tmpBuf);
		$res 		= @socket_send($this->hnd,$tmpBuf,$iBufLen,0);
		if($res === FALSE){
			$this->error();
		}elseif ($res < $iBufLen){
			$tmpBuf 	= substr($tmpBuf,$res);
			$this->send($tmpBuf);
		}
	}
	public function write($sBuf,$iTimeOut=null){
		return $this->send($sBuf,$iTimeOut);
	}
	public function recv($iTimeOut=null){
		if(!$this->bConnected){
			$this->error("Socket error. Cannot read any data on a closed socket.");
			return;
		}
		$vSocket		= array($this->hnd);
		$this->sBuffer 	= null;
		$buf			= null;
		$iBufLen		= 4096;
		$ReadTimeOut	= strlen($iTimeOut) ? $iTimeOut : $this->iReadTimeOut;
		try {
			while(($rr = @socket_select($vSocket,$vWrite = null,$vExcept = null,$ReadTimeOut))===FALSE);
			if($rr==0) return;
			$res			= @socket_recv($this->hnd,$buf,$iBufLen,0);
			while($res){
				$this->sBuffer 	.=	$buf;
				$buf			 = null;
				while(($rr = @socket_select($vSocket,$vWrite = null,$vExcept = null,$ReadTimeOut))===FALSE);
				if($rr==0) break;
				$res			 = @socket_recv($this->hnd,$buf,$iBufLen,0);
			}
		}
		catch (Exception $e) {
			$this->error();
		}
		return $this->sBuffer;
	}
	public function read($iTimeOut=null){
		return $this->recv($iTimeOut);
	}
	public function sendandrecive($sBuf){
		$this->send($sBuf);
		return $this->recv();
	}
}
?>