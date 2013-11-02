<?

class BB_Error{
	/// The timestamp
	public $timeStamp;
	/// The backtrace
	public $callPath;
	/// The message  
	public $msg;
	/// The complete text of the log entry
	public $logEntry;
	
	public function BB_Error($msg){
		if($msg=="") return;
		$this->msg=$msg;
		if(BB_DEBUG){
			$backtrace = debug_backtrace();
			
			$callPath = $backtrace[1]['function'];
			$callPath .= ' [Line ' . $backtrace[1]['line'];
			$callPath .= '; File: ' . $backtrace[1]['file'] . ']';
			
			for ($n=2; $n<=20; $n++) {
				if (isset($backtrace[$n]['function'])) {
					$callPath .= '<-' . $backtrace[$n]['function'];
				}
			}
			
			$logEntry = date("Y.m.d H:i:s (l)") . ": Error in function: " . $callPath;
			$this->timeStamp = date("Y.m.d H:i:s (l)");
			$this->callPath=$callPath;
			
			if ( strlen($msg) > 0 ) {
				$logEntry .= "; Description: " . $msg;
			}
			$this->logEntry=$logEntry;
			$logEntry .= "\n";

			
			if(@!file_exists(BB_ERROR_LOGFILE)) $this->zeroLog();
			if(BB_ERROR_LOGGING){
			 $f=fopen(BB_ERROR_LOGFILE,"at");;
			 fwrite($f,$logEntry);
			 fclose($f);
			 }
		 }
		 if((BB_DEBUG && @$_SESSION['err'] == 1)){
		 	/*
		 	echo("<pre>");
		 	var_dump(debug_backtrace());
		 	echo("</pre>");
		 	*/
		 	if(substr($msg,0,5)=="Fatal") die("<br/>".$logEntry); else echo("<br/><span style='color:red;font-weight:bold;'>".$logEntry."</span><br/>");
		 }
		 
		 
	}

	static function zeroLog(){
		//$now=$this->getNow();
		/*
if(	!isset($this) ) { 
			$bb=new BB_Error("");
			$bb->zeroLog();
			return;
		}
*/
		$now=BB_Error::getNow();
		 $f=fopen(BB_ERROR_LOGFILE,"wt");
		 if($f){
		 	fwrite($f,"$now LOG RESET \n");	
			 fclose($f);
		 }
		
	}
	
	static function getNow(){
		return( date("Y-m-d H:m:s",strtotime("now")) );
	} 
	
	public function __toString(){
		return $this->logEntry;
	}
}

?>