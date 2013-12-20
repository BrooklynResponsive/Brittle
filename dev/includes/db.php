<?php

// ripped from another project. Sorry, bizbash! 



//! The database connection manager class. 
/**
Using information in bb_config.php, configures the connection to the mysql database. 
*/
class BB_DB{
	/// The holder for the database connection.
	public $db; 
	/// The holder for the memcached connection
	static $mcd;
	/// Prefix for memcache keys
	static $mcdKeyPrefix;
	/// RelationTypes is by far the most commonly called table. We make it a static array here to reduce database load.
	static $relationTypesCache=false;


	public function BB_DB(){
		$this->setUpDB();
		$this->setUpMemcache();
		if(!self::$relationTypesCache){
			$r=$this->q("select * from relationTypes");
			self::$relationTypesCache = array();
			while(@ $rr=mysql_fetch_assoc($r)){
				self::$relationTypesCache[]=$rr;
			}
		}
	}
	
	protected function setUpDB(){
		if(isset($this->db)) return;
		$this->db=mysql_connect(BB_DB_SERVER, BB_DB_USER, BB_DB_PASSWORD);
		mysql_query("set names utf8",$this->db); 
		if(!$this->db) { trigger_error("Unable to connect to database ".BB_DB_SERVER.". Check your bb_config.php file",E_USER_ERROR); die(); }
		$try=mysql_select_db(BB_DB_NAME,$this->db);
		if(!$try) { trigger_error("Unable to switch to database ".BB_DB_NAME.". Check your bb_config.php file",E_USER_ERROR); die(); }
	}
	
	protected function setUpMemcache(){	
		global $MEMCACHED_SERVER;
		self::$mcdKeyPrefix=BB_DB_NAME;
		if(@MEMCACHED_ACTIVE_FOR_DB_PROXIES && !isset(self::$mcd) && class_exists("Memcache") && isset($MEMCACHED_SERVER)) { 	
			self::$mcd=new Memcache();
			foreach($MEMCACHED_SERVER as $SERVER){
				$try=@self::$mcd->addServer($SERVER, MEMCACHED_PORT );
				if(!$try) { 
					self::$mcd=false;
					//echo("<!-- Memcached server $SERVER fail -->"); 
					return;
				}
			}
			//now double check server status
			$sc=0;
			foreach( self::$mcd->getExtendedStats() as $stat){
				$sc++;
				if($stat==false) { self::$mcd=false; /* echo("<!-- Memcached server fault -->"); */ }
			}
			//if(self::$mcd!=false) echo("<!-- $sc Memcached servers good -->");
		}

	}
	
	/**
	\brief Wrapper for all bb_data queries
	
	\param string $query the query to execute
	
	\return MYSQL resource result on success, false on failure. Throws an error of level E_USER_ERROR if global BB_DEBUG is true.
	*/
	
	public function q($query){ 

		mysql_select_db(BB_DB_NAME,$this->db);
		//$then=microtime(true);
		$ret=mysql_query($query,$this->db); 
		if(!$ret) { if(BB_DEBUG) { new BB_Error("Error with query: $query //  ".mysql_error($this->db) ); } } 
		//$elapsed=(microtime(true)-$then)*1000;
		/*
if(strtotime("now")>strtotime("2012-04-03 06:57:00") && strtotime("now")<strtotime("2012-04-03 07:02:00") ) { 
			global $BB_Utils; 
			if($BB_Utils) {
				

				$query = preg_replace("/(parentID=|id\ ?=\ ?|sourceRow=|targetRow=)[0-9]+/",'$1NUM',$query);
				$query = preg_replace("/(sourceTable)=\ ?'[a-zA-Z]+'|(targetTable)=\ ?'[a-zA-Z]+'/",'$1=TABLE',$query);
				if(strpos( $query, "LocalListingCache")) $query="LocalListingCache request";


				$BB_Utils->raeQ("insert into querySamples(query,took,file) values ('".mysql_real_escape_string($query)."', $elapsed, '".mysql_real_escape_string($_SERVER['SCRIPT_NAME'])."')"); 
			}
		}
*/
		return $ret;
	}

	
	/**
		\brief Flushes the entire memcached cache. Use with caution.
	
	*/
	public function mcdFlushAll(){
		if(self::$mcd ) self::$mcd->flush();
	}
	
	/**
		\brief Flushes this entry from the memcached cache. 
	
	*/
	public function mcdFlushMe(){
		if(self::$mcd ) self::mcdDelete("$this->tableName".$this->id);
	}
	
				
	/**
		\brief Takes care of attempting to get a value from the memcache.
		
	*/
	static function mcdGet($key){ 
		if(self::$mcd) return(self::$mcd->get(self::$mcdKeyPrefix.$key));
		return false;
	}
			
			
	/**
		\brief Takes care of setting a value in memcache. Attempts to replace, then set, which is how you're supposed to do it, apparently.
		\param string $key The key to use to store the object
		\param mixed $value The item to store
		\param int $compress The constant MEMCACHE_COMPRESSED or nothing. 
		\param int $expire Optional; seconds to keep item in cache
		
	*/
	static function mcdUpdateCache($key, $value){
		$key=self::$mcdKeyPrefix.$key;
		if(func_num_args()>3) $expire=func_get_arg(3); else $expire=0;
		if(func_num_args()>2) $compress=func_get_arg(2); else $compress=false;
		//$compress=MEMCACHE_COMPRESSED;
		if(self::$mcd){
			$try=self::$mcd->replace($key, $value, $compress, $expire);
			if($try){ 
	 			//echo("<!-- replace memcache $key -->");
				return true;
		 	}else{
		 		$try=self::$mcd->set($key, $value, $compress, $expire);	
		 		if($try){ 
		 			//echo("<!-- Set memcache $key -->");
		 			return true;
		 		}else{
			 		//echo("<!-- Failed to set Memcache key $key -->");
			 		return $try;	
		 		}
		 	} 
		 }else{ 
		 	//echo("<!-- failed to find mcd -->");
		 	return false;
		 }
	}
	
	/**
		\brief Takes care of removing a value in memcache. 
		
	*/
	static function mcdDelete($key){
		if(self::$mcd) {
			$try=self::$mcd->delete(self::$mcdKeyPrefix.$key);
		}
	}
	
	
	/**
	\brief returns an SQL-formatted string representing the current time.
	
	*/
	public function SQLTimeNow(){
		return( date ("Y-m-d H:i:s"));
	}
	
	/**
	\brief returns an SQL-formatted string representing the current date.
	
	*/
	public function SQLDateNow(){
		return( date ("Y-m-d"));
	}
	
	
	/**
	\brief returns an SQL-formatted string representing the time submitted.
	
	*/
	public function SQLTime($time){
		if(!is_numeric($time)) $time=strtotime($time);
		return( date ("Y-m-d H:i:s",$time) );
	}
	
	
	/**
	\brief returns an SQL-formatted string representing the date submitted.
	
	*/
	public function SQLDate($date){
		if(!is_numeric($date)) $time=strtotime($date);
		return( date ("Y-m-d", $date ) );
	}
	
	/*
	\brief Returns the properties of this object as an array
	
	The system uses memcache to store data from class objects and doesn't expect class methods to be available on retrieved data, so the memory used to store them is wasted. To avoid this, the cacheableMe function strips methods and static variables out by iterating on $this and returning the results as an array. 
	
	*/
	public function cacheableMe(){
		foreach($this as $key => $val){
			//echo("<br> processing $key");
			$retval->{$key} = $this->cacheableMeWorker($val);
		}
		return $retval;
	}
	
	public function cacheableMeWorker($val){
		//echo("<br> --- ".gettype($val));
		switch(gettype($val)){
			
				case "boolean":
				case "integer":
				case "double":
				case "string":
				case "NULL":
				return $val;
				break;
				case "array":
					$retval=array();
					foreach($val as $k=>$v){
						$retval[$k] = $this->cacheableMeWorker($v);
					}
					return $retval;
				break;
				case "object":
					//echo("<br> --class: ".get_class($val));
					if(method_exists($val,"cacheableMe")){
						//echo("<br> -- calling cacheable on ob");
						return $val->cacheableMe();
					}else{
						//echo("<br> -- ob has no cacheable");
								
								foreach($val as $k => $v){
									//echo("<br> sub-processing $k");
									$retval->{$k} = $this->cacheableMeWorker($v);
								}
								return $retval;
					}
				break;
				case "resource": //do nothing
					return NULL;
				break;
				default:
					new BB_Error("Fatal Error: couldn't figure out object type in cacheableMeWorker()");
				break;
			
			}
	}
	
	
	
}

/**
	@brief Base class for structures that are images of database tables that use an int PRIMARY key.
	
	Provides access to core functions: 
	- read/write functions
	- universal getter/setter function val()
	- relationship manager functions doRelations() etc
	- automatic call of initialization functions in inheriting classes 

*/
class BB_DB_Obj extends BB_DB{ 
	///The list of fields <strong>other than the ID field</strong> that represent this structure in the database. 
	protected $fieldList; 
	///The table in the database to which this class refers. 
	protected $tableName; 
	/// The primary key ID field of this record. Checked in writeMe.
	public $id; 
	/// Set in inheriting classes that should not use the cache
	static $doNotCache;
	
	
	/**
	\brief Constructor.
	
	expects at least $tableName, $fieldList and optionally $initArray which can be either an associative array of props or an id of a DB record
	
	\param string $tableName Table in the database to which this class refers.
	\param array $fieldList The column names of this table to which this class can legally write. 
	\param mixed $initArray <b>optional</b> Either an associative array of values to initialize the class OR a number indicating the ID field of a record in the $tableName table with which to initialize the class.

	*/
	public function BB_DB_obj($tableName, $fieldList){ 
		parent::__construct();
		$this->id=0;
		if(property_exists($this, 'isCopyOf')) $this->isCopyOf=0;
		if(func_num_args()==0) return;
		$this->tableName=func_get_arg(0);
		$this->fieldList=func_get_arg(1);
		if(func_num_args()==3){
			$initArray=func_get_arg(2);
			if(is_array($initArray)){
				while(list($key, $val) = each($initArray)){
					$this->{$key} = $val;
				}
			}
			if(is_numeric($initArray)){
				$this->readMe($initArray);
			}
		}
	}

	/**
	\brief Populates class properties from the database.
	\param number $id The unique ID of a row in $tableName to read into this class.
	\param bool $loadLinkedObjects Optional, defaults to true. Setting this to false fetches only the values of the columns in the row of the table for which this object is a proxy.
	\return true on success, false if no row found. Throws an error if BB_DEBUG is true.
	*/
	public function readMe($id){	
		if(func_num_args()==2) $loadLinkedObjects=(bool)func_get_arg(1); else $loadLinkedObjects=true;
		/// Patch for table shares
		if($this->tableName=='category' || $this->tableName=='story'){
			global $BB_Utils;
			$get = $BB_Utils->raeQ("select * from $this->tableName where id=$id");		
			if(!$get || mysql_num_rows($get)==0)  { new BB_Error("No row found in $this->tableName at ID $id"); return false; }	
			$getr = mysql_fetch_assoc($get);
		}else{ // All normal queries here
			// try fetching from memcached.
			$try=false; 
			if(!self::$doNotCache) $try=self::mcdGet("$this->tableName".$id);
			if($try) $getr=$try; 
			else {
				$get = $this->q("select * from $this->tableName where id=$id");
				if(!$get || mysql_num_rows($get)==0)  { new BB_Error("No row found in $this->tableName at ID $id"); return false; }	
				$getr = mysql_fetch_assoc($get);
				if(!self::$doNotCache) self::mcdUpdateCache("$this->tableName".$id, $getr);
			}
		}

		foreach($this->fieldList as $field){
			if(isset($getr[$field])){
				 $this->{$field} = $getr[$field];
			}
		}
		$this->id=$id;
		if($loadLinkedObjects) $this->getMyRelations();
		return true;
	
	
	}
	
	/**
	\brief Populates class properties from the database, switching to draft copies of the objects assigned to the current user, if available.
	\param number $id The unique ID of a row in $tableName to read into this class.
	\return true on success, false if no row found. Throws an error if BB_DEBUG is true.
	*/
	public function readMePreferDrafts($id){	
		if($this->readMe($id)){
			$try=$this->isBeingEditedByMe();
			if($try && $try!=$this->id) { //draft exists but we're not it
				return $this->readMePreferDrafts($try); 		 
			}else{ //draft exists and we are it
				$this->getMyRelations(true);
				return true;
			}
		}else{  //couldn't read ID
			new BB_Error("No row found in $this->tableName at ID $id"); return false; 
		}
			
	}
	
	/**
	\brief Writes the properties of the class into the database. 
	
	Writes the properties listed in $fieldList into the table specified in $tableName. If $this->id is non-zero, then the object represents an existing row in the database and a REPLACE statement is used, otherwise an INSERT command is used.
	Some tasks may need to be performed on insert depending on class so we call initForFirstWrite, a method that initializes info for the first INSERT and is contained in the inheriting class, if it exists.
	<strong>Note that this function does not check permissions.</strong> Permissions checks in the CMS should be handled upstream of this function (for example, in CMSUserInput.class.php)
	
	\return true on success, false on SQL failure.
	*/
	public function writeMe(){
			$wa=array();
			$fa=array();
			
			// If we're creating this record from scratch we need to call the init function to fill in some defaults.
			if($this->id==0 && method_exists($this,"initForFirstWrite")) $this->initForFirstWrite();
			
			foreach($this->fieldList as $field){
				if(isset($this->{$field})){ 
						$wa[]=($this->{$field}==='')?"DEFAULT ":"'".mysql_real_escape_string($this->{$field})."' ";
						$fa[]=$field;
				}
			}

		if($this->id<>0){ 
			$FL=( count($fa)!=0 )?( ", ".implode(", ",$fa).") values ($this->id, ".implode(",",$wa).")" ):") values ($this->id)";
			$sql="replace into $this->tableName (id $FL";
			if($this->q($sql) ) {
					//invalidate mcd cache
					self::mcdDelete("$this->tableName".$this->id);
					if(!$this->setMyRelations()) return false;
					return true; 
				} else return false; 
			
		}else{ 
			
			if( count($fa)!=0 )
				$sql="insert into $this->tableName (".implode(", ",$fa).") values (".implode(",",$wa).")";
			else
				$sql="insert into $this->tableName select max(id)+1 from $this->tableName";
			if($this->q($sql) ) {
				$this->id=mysql_insert_id($this->db);
				//invalidate mcd cache
				self::mcdDelete("$this->tableName".$this->id);
				
				if(!$this->setMyRelations()) return false;
				return true; 
			}else return false;
			
		} 
	}	


	/**
	\brief Deletes all mention of this object in the database
	
	\param boolean $forceDelete Overrides the permission check that is usually performed to ensure the user has commit permissions on the table they're modifying. Used, for example, in the CMSUserInput class, which has already checked that the record is assigned to the user and that it is a draft copy. Optional.
	
	Kills the resource, its draft editing copies, if any, and all relations table references to it and its copies. If used on a draft copy, leaves the original intact. Note that the object itself is not unset at the end so you can still access its ID, for example, even though that ID does not exist anymore in the database.
	
	\remark <strong>Use with caution.</strong> If you're looking at this function you probably should be considering bb_deduper.php. 
	
	\return true on success, false on failure.
	*/
	public function deleteMe(){
			$forceDelete=(func_num_args()==1) ? (bool)func_get_arg(0) : false;
			global $curUser;
			if(!$curUser->hasCommitPermission($this->tableName, 'ALL') && !$forceDelete) return false;
			
			//invalidate mcd cache
			self::mcdDelete("$this->tableName".$this->id);
			
			$purges=array();
			$purges[]=$this->id;
			//get all draft copies
			if(property_exists($this,"isCopyOf")){			
				$t=$this->q("select id from $this->tableName where isCopyOf=$this->id and isCopyOf!=0");
				while($tt=mysql_fetch_object($t)){
					$purges[]=$tt->id;
				}
			}
			$purgeIDs=implode(",",$purges);

			$try=$this->q("delete from $this->tableName where id in ($purgeIDs)");
			if(!$try) return false;
			
			$myTables=$this->relationTableNames("SOURCE");
			foreach($myTables as $relTable){
				$try=$this->q("delete from $relTable where sourceRow in ($purgeIDs)");
				if(!$try) return false;
			}
			$myTables=$this->relationTableNames("TARGET");
			foreach($myTables as $relTable){
				$try=$this->q("delete from $relTable where targetRow in ($purgeIDs)");
				if(!$try) return false;
			}				
			
			return true;		
	}
	
	
	/**
	\brief Utility function for retrieving names of relation tables relevant to this class either as source or target table.
	
	The relationships between this class and others are stored in tables with the prefix 'rel_'. The names are taken from the type column of the relationTypes table. 
	
	\param string $sourceOrTarget when set to 'SOURCE' returns only table names where this table is a source. When set to 'TARGET' returns only table names where this table is a target. Other values or no value returns both.
	
	\return An array of strings representing names of the relation tables relevant to this class, possibly empty.
	
	*/
	
	public function relationTableNames(){
		if(func_num_args()==1){
			$sourceOrTarget=func_get_arg(0);		
		}else $sourceOrTarget='';
		
		switch($sourceOrTarget){
			case "SOURCE":
				$try=$this->q("select type from relationTypes where sourceTable='$this->tableName' order by type"); 
			break;
			case "TARGET":
				$try=$this->q("select type from relationTypes where targetTable='$this->tableName' order by type");
			break;
			default:
				$try=$this->q("select type from relationTypes where targetTable='$this->tableName' or sourceTable='$this->tableName' order by type");
			break;
		}
		$tn=array();
		while($r=mysql_fetch_object($try)){
			$tn[]="rel_".$r->type;
		}
		return $tn;
	
	} 
	
	/**
	\brief returns the value of the type column of relationTypes for a given field
	
	\param string $fieldName the name of the property to check
	
	\return false if the property is not a link field. Otherwise, the relationship type.
	*/
	public function relationTypeForField($fieldName){
		if($fieldName=='') return false;
		//the story proxy object has a tableName of raecms.story will give us trouble here if we don't parse out the raecms
		$tableName=explode(".",$this->tableName);
		$tableName=end($tableName);
		
		$tt=$this->q("select type from relationTypes where sourceTable='$tableName' and classVariable='".mysql_real_escape_string($fieldName)."'");
		if(!$tt) return false;
		if(mysql_num_rows($tt)==0) return false;
		$ttt = mysql_fetch_object($tt);
		return $ttt->type;
	}
	
	/**
	\brief returns the value of the sourceTable column of relationTypes for a given type
	
	\param string $type the name of the type to look for. This can be the result of a relationTableNames method call, as the "rel_" will be stripped automatically.
	
	\return false if the property is not a link field. Otherwise, the source table name
	*/
	public function relationSourceTableForType($type){
		if($type=='') return false;
		if(substr($type,0,4)=="rel_") $type=substr($type,4);
		$tt=$this->q("select sourceTable from relationTypes where type='".mysql_real_escape_string($type)."'");
		if(!$tt) return false;
		if(mysql_num_rows($tt)==0) return false;
		$ttt = mysql_fetch_object($tt);
		return $ttt->sourceTable;
	}
	
	/**
	\brief returns the value of the targetTable column of relationTypes for a given type
	
	\param string $type the name of the type to look for. This can be the result of a relationTableNames method call, as the "rel_" will be stripped automatically.
	
	\return false if the property is not a link field. Otherwise, the source table name
	*/
	public function relationTargetTableForType($type){
		if($type=='') return false; 
		if(substr($type,0,4)=="rel_") $type=substr($type,4);
		$tt=$this->q("select targetTable from relationTypes where type='".mysql_real_escape_string($type)."'");
		if(!$tt) return false;
		if(mysql_num_rows($tt)==0) return false;
		$ttt = mysql_fetch_object($tt);
		return $ttt->targetTable;
	}

	
	
	/**
	\brief Universal getter/setter
	
	This is the getter/setter available to all child classes. With one argument it's a getter, with two it's a setter. For any property, the get or set function can be overridden by creating a method corresponding to the property name with 'set_' or 'get_' prepended to the property name. Classes can be agnostic about the existence of specific getters and setters by using this function. 
	
	\param mixed $property the property
	\param mixed $value <b>optional</b> the value to set
	
	\return The current value of $property (getter), or the new value of $property (setter)
	*/
	public function val(){ //the getter/setter function 
		// called with one argument is get, two is set
		// overridden by any explicitly defined local functions called set_<func> or get_<func> 
	
		if(func_num_args()==2){
			$label=func_get_arg(0);
			$value=func_get_arg(1);
			if(method_exists($this,"set_$label")){
				if(is_string($value)) { 
						eval('$retval=$'."this->set_$label('".str_replace("'","\'",$value)."');");
						return $retval; 
					}
				 	else return(eval('$'."this->set_$label($value);"));
			}else{
				$this->{$label}=$value;
				return $value;
			}
		}elseif(func_num_args()==1){
			$label=func_get_arg(0);
			if(method_exists($this,"get_$label")){
				eval('$retval=$'."this->get_$label();");
				return $retval;
			}else{
				if(property_exists($this,$label)) return $this->{$label};
					else return null;
			}
		}
	}
	
	
	
	
	/**
	\brief Processes the relationTypes table to determine which relations to set in relation to the class.
	
	Calls the doRelations function as necessary after processing relationTypes. 
	
	\return true on success, false on failure
	*/
	public function setMyRelations(){
		//$typeQ=$this->q("select * from relationTypes where sourceTable='$this->tableName'");
		$retval=true;
		for($t=0;$t<count(self::$relationTypesCache);$t++){
			$typeRow=self::$relationTypesCache[$t];
			if($typeRow['sourceTable']!=$this->tableName) continue;
		//while($typeRow=mysql_fetch_assoc($typeQ)){
			if($typeRow['multiple']==0 && @is_array($this->{$typeRow['classVariable']}) && count($this->{$typeRow['classVariable']})>1 ) new BB_Error("Multiple relations found for $this->tableName ID $this->id of type ".$typeRow['type']." when type should be unique per ID");
			if(!$this->doRelations($typeRow['type'], $this, $this->{$typeRow['classVariable']})) $retval=false;
		}
		return $retval;
	
	}
	
	/**
	\brief Processes the relationTypes table to determine which relations to get for this class.
	
	Pulls records from the tables specified in relationTypes according to the row IDs in relations, instantiating objects and assigning them to the variable specified by the <b>classVariable</b> column in the relationTypes table.
	\param boolean $preferDrafts A switch thrown by the readMePreferDrafts function that will load draft copies assigned to this user preferentially.
	
	\return true on success, false on failure
	*/
	public function getMyRelations($preferDrafts=false){
		

			$sqlToDo=array();
			for($t=0;$t<count(self::$relationTypesCache);$t++){
				$typeRow=self::$relationTypesCache[$t];
				if($typeRow['sourceTable']!=$this->tableName) continue;
				unset($this->{$typeRow['classVariable']}); //necessary because this may not be the first time we're running the readMe method.
				$sqlToDo[]="select '".$typeRow['classVariable']."' as classVariable, ".$typeRow['multiple']." as multiple, ".$typeRow['loadObjects']." as loadObjects, '".$typeRow['sourceTable']."' as sourceTable, '".$typeRow['targetTable']."' as targetTable, '".$typeRow['type']."' as type,  t.* from rel_".$typeRow['type']." t where sourceRow=$this->id";
			}
			
			if(count($sqlToDo)){
				$sqlRel=implode(" UNION ",$sqlToDo)." order by classVariable, ordinal";
				$relQ=$this->q($sqlRel);
			}else{
				return true;
			} 

		
		while($relRow = mysql_fetch_object($relQ)){
			
			if($relRow->multiple==0 && isset(${$relRow->type})) new BB_Error("Multiple relations found for $this->tableName ID $this->id of type $relRow->type when type should be unique per ID");
			if(!property_exists($this, $relRow->classVariable)) { new BB_Error("Variable $relRow->classVariable specified for BB_".$relRow->sourceTable." does not exist."); return false; }
			
			if(!isset($this->{$relRow->classVariable})) $this->{$relRow->classVariable} = array();
			//There are two types of relationType table links, depending on the value of the loadObjects column. If loadObjects=1 then the classVariable is populated with an array of class instances. If not, the classVariable is populated with an array of ID numbers.
			if($relRow->loadObjects==1){ 
				$cName="BB_".$relRow->targetTable; 
				$newClass=new $cName();
				if($preferDrafts)
					$newClass->readMePreferDrafts($relRow->targetRow);				
				else
					$newClass->readMe($relRow->targetRow);

				

				
				if($relRow->multiple==0) {
					$this->{$relRow->classVariable}=$newClass;
				}else{
					$this->{$relRow->classVariable}[]=$newClass;
				}
				
			}else{ //loadObjects is zero, just add the ID.
				
				if($relRow->multiple==0) {
					$this->{$relRow->classVariable}=$relRow->targetRow;
				}else{
					$this->{$relRow->classVariable}[]=$relRow->targetRow;
				}
					
			
			}
			${$relRow->type}=true;
		
		}
		
		/*
for($t=0;$t<count(self::$relationTypesCache);$t++){
			$typeRow=self::$relationTypesCache[$t];

			if($typeRow['sourceTable']!=$this->tableName) continue;

			$sql="select * from rel_".$typeRow['type']." where sourceRow=$this->id order by ordinal";

			$relQ=$this->q($sql);
			if($typeRow['multiple']==0 && mysql_num_rows($relQ)>1) new BB_Error("Multiple relations found for $this->tableName ID $this->id of type ".$typeRow['type']." when type should be unique per ID");
			$this->{$typeRow['classVariable']} = array();
			while($relRow=mysql_fetch_object($relQ)){
				//There are two types of relationType table links, depending on the value of the loadObjects column. If loadObjects=1 then the classVariable is populated with an array of class instances. If not, the classVariable is populated with an array of ID numbers.
				if($typeRow['loadObjects']==1){ 
					$cName="BB_".$typeRow['targetTable']; 
					$newClass=new $cName();
	
					if($preferDrafts)
						$newClass->readMePreferDrafts($relRow->targetRow);				
					else
						$newClass->readMe($relRow->targetRow);
	
						
					if(!property_exists($this, $typeRow['classVariable'])) { new BB_Error("Variable ".$typeRow['classVariable']." specified for BB_".$typeRow['sourceTable']." does not exist."); return false; }
					if($typeRow['multiple']==0) $this->{$typeRow['classVariable']}=$newClass;
						else $this->{$typeRow['classVariable']}[]=$newClass;
				}else{ //loadObjects is zero, just add the ID.
					if($typeRow['multiple']==0) $this->{$typeRow['classVariable']}=$relRow->targetRow;
						else $this->{$typeRow['classVariable']}[]=$relRow->targetRow;
				
				}
			} 
		}
*/
		return true;
	}
	
	
	/**
	\brief Sets up the entries in the relations table representing a link between parent class and a class attached in a linked field
	
	
	
	\param string $type the relation type, available in the table relationTypes. Used to find the relevant relation table.
	\param obj $sourceClass the object providing the row number of the source class
	\param array $targetClasses array of objects or id numbers to link to this source class via the <b>relations</b> table.
	
	\remark The $targetClasses array is either an array of objects or of IDs of objects - this is specified in the loadObjects column of the relationTypes table. As a courtesy, this function corrects objects incorrectly added to ID number arrays and vice versa by converting them silently.
	
	\return true on success, false on failure
	*/
	public function doRelations($type, $sourceClass, &$targetClasses){ 
		//echo("<br/>doRel called for $type on $sourceClass->id and ".print_r($targetClasses,true));
		$typecheck = $this->q("select * from relationTypes where type = '$type'"); 
		if(!mysql_num_rows($typecheck)) { $a=new BB_Error("doRelations called with undefined relation type"); return false;}
		$tr=mysql_fetch_object($typecheck);
		$isObjectArray=(bool)$tr->loadObjects; 
		$deleteAll=false; // a switch, used below
		if(empty($targetClasses)) $deleteAll=true;
		//to simplify the code we will always treat targetClasses as an array. If it's not a multiple link field that means we need to convert it to an array but remember to convert it back when we're done.
		if(!is_array($targetClasses)) { 
			$unConvertArray=true; 
			$targetClasses=(!empty($targetClasses))?array($targetClasses):array();
			
		}else $unConvertArray=false;
		$targetClasses = array_values($targetClasses);

		if(count($targetClasses)==0) $deleteAll=true;
		if($deleteAll) { //this is a shortcut; if there are no objects to link to we know we can just clear everything.
			$sql="delete from rel_$type where sourceRow=".$sourceClass->val('id'); //clear existing relations
			$this->q($sql);
			if($unConvertArray) unset($targetClasses);
			return true;
		}// ...otherwise we'll compare the ones in the DB with the ones in targetClasses and then vice versa. We do this to reduce the number of insert/delete statements on these important tables.
		
		$obName="BB_".$tr->targetTable;
		//Courtesy conversion of object arrays to ID arrays and vice versa in case we added the wrong kind by accident
		for($i=0;$i<count($targetClasses);$i++){
			$to=$targetClasses[$i];
			if($isObjectArray && !is_object($to) && is_integer($to) && $to!=0) $targetClasses[$i]=new $obName($to);
			if(!$isObjectArray && is_object($to) && property_exists($to,'id') && $to->id!=0) $targetClasses[$i]=$to->id;
		}
		
		//Check DB contents against targetClasses
		$c=$this->q("select * from rel_$type where sourceRow=$sourceClass->id");
		$sqlToDo=array();
		while($check=mysql_fetch_object($c)){
			$thisOrd=$check->ordinal;
			$thisID=$check->targetRow;
			//echo("<br/>Checking $thisID [ $thisOrd ] against targetclasses ");
			if($isObjectArray){ //targetClasses is an array of class instances
				if(!isset($targetClasses[$thisOrd]) || $targetClasses[$thisOrd]->id!=$thisID){ //this record does not match the contents of targetClasses (either it's the wrong ID or in the wrong position. We remove.
					$sqlToDo[]="(sourceRow=$sourceClass->id and targetRow=$thisID and ordinal=$thisOrd)";
					//echo("<br/>Did not find a match in the DB, so we deleted where sourceRow=$sourceClass->id and targetRow=$thisID and ordinal=$thisOrd");
				}//else echo("<br/>...was left in place.");
			}else{ //targetClasses is an array of ID numbers
				if(!isset($targetClasses[$thisOrd]) || $targetClasses[$thisOrd]!=$thisID){ //this record does not match the contents of targetClasses (either it's the wrong ID or in the wrong position. We remove.
					$sqlToDo[]="(sourceRow=$sourceClass->id and targetRow=$thisID and ordinal=$thisOrd)";
					//echo("<br/>Did not find a match in the DB, so we deleted where sourceRow=$sourceClass->id and targetRow=$thisID and ordinal=$thisOrd");
				}//else echo("<br/>...was left in place.");
			}
			
		}
		if(count($sqlToDo)){
			$sqlDelete="delete from rel_$type where ".implode(" OR ",$sqlToDo);
			$this->q($sqlDelete);
		}
		//Now the DB rows that are no longer relevant to this object have been removed. We need to write the links for the elements of targetClasses, but not if they are left over (still in the DB) from the previous loop.
		
		//Check targetClasses against DB contents
		$sqlToDo=array();
		for($i=0;$i<count($targetClasses);$i++){
			$targetClass=$targetClasses[$i];
			if($isObjectArray){//targetClasses is an array of class instances
				//echo("<br/>Checking $targetClass->id [ $i ] against DB contents ");
				if($targetClass->id==0 || $sourceClass->id==0) new BB_Error("Fatal error: attempting to set up relations with objects not yet written to the database: <pre>".print_r($sourceClass,true).print_r($targetClasses,true)."</pre>");
				$c=$this->q("select * from rel_$type where sourceRow=$sourceClass->id and targetRow=$targetClass->id and ordinal=$i");
				if(mysql_num_rows($c)==0){ //this row is not present and needs to be inserted.
					//echo("<br/>check rel_$type where sourceRow=$sourceClass->id and targetRow=$targetClass->id and ordinal=$i but did not find match");
					$sqlToDo[]="($sourceClass->id, $targetClass->id, $i)"; 

				}//else echo("<br/>...and it was found");
			}else{ //targetClasses is an array of ID numbers
				//echo("<br/>Checking $targetClass->id [ $i ] against DB contents ");
				if($targetClass==0 || $sourceClass->id==0) new BB_Error("Fatal error: found zero ID in class variable array for relation $type in <pre>".print_r($sourceClass,true)."</pre>");
				$c=$this->q("select * from rel_$type where sourceRow=$sourceClass->id and targetRow=$targetClass and ordinal=$i");
				if(mysql_num_rows($c)==0){ //this row is not present and needs to be inserted.
					//echo("<br/>check rel_$type where sourceRow=$sourceClass->id and targetRow=$targetClass->id and ordinal=$i but did not find match");
					$sqlToDo[]="($sourceClass->id, $targetClass, $i)"; 

				}//else echo("<br/>...and it was found");
			}
		}
		if(count($sqlToDo)) {
			$sqlInsert="insert into rel_$type (sourceRow, targetRow, ordinal) values ".implode(", ",$sqlToDo);
			$this->q($sqlInsert);
		}
		if($unConvertArray) $targetClasses=$targetClasses[0];
		return true;
	}
	
	/**
	
	\brief Commits changes to this class and all its linked class instances.
	
	Analagous to the save code in CMS which deleted the record referred to by archstory and changed the ID of the record to that ID, this code traverses the current class and linked classes via the relations tables and commits draft copies of it and linked classes to the database. Rather than make the change of the ID number and associated properties directly on the database, we change the id in the object and call writeMe, which necessitates a deleteMe operation on the original draft copy.	
	
	\returns true on success, BB_Error on failure
	*/
	public function commitChanges(){
		// First, just double check that this and the original both exist in the database. Because of the structure ot the cascadingCommit function, objects can be deleted if they exist at more than one place in the resource object tree since this function would get called on a draft object that is no longer in the database and the original would be deleted.
		
		$rcheck=$this->q("select id from $this->tableName where id=$this->id or id=$this->isCopyOf");
		if(mysql_num_rows($rcheck)!=2) return true;
		// Check permissions
		global $curUser;
		if(!$curUser->hasCommitPermission($this->tableName,'ALL')) return new BB_Error("You don't have permission to commit this object type");
		if(!$this->isBeingEditedByMe()) return new BB_Error("This object is not assigned to you.");
		// Per-field commit privileges are checked in the userInput class function commit().
		if(!property_exists($this, 'isCopyOf') || $this->isCopyOf==0  || $this->id==0) return new BB_Error("Not a draft editing copy. Can't commit.");
		
		$originalID=$this->isCopyOf;
		if(property_exists($this,'isPublished')){ //we need to preserve this attribute from the old copy through the commit
			$origOb = clone $this;
			$origOb->readMe($this->isCopyOf);
			$preservePubVal=$origOb->val('isPublished');
		}else $preservePubVal='NO';
		$draftID=$this->id;
		// delete original
		$this->q("delete from $this->tableName where id=$originalID");
		self::mcdDelete("$this->tableName".$originalID);
		// change draft id to original id
		$this->id=$originalID;
		// change  isAssignedTo and isCopyOf as appropriate.
		$this->isCopyOf=0;
		if(property_exists($this,"isAssignedTo")) { $this->isAssignedTo=""; }
		if($preservePubVal!='NO') $this->val('isPublished',$preservePubVal);
		//write class
		$myTables=$this->relationTableNames("SOURCE");
		foreach($myTables as $relTable){
			$this->q("delete from $relTable where sourceRow=$originalID");
			$this->q("update $relTable set sourceRow=$originalID where sourceRow=$draftID");
		}
		// Because the image manager forces display of drafts in the image pool, it is possible for drafts to be linked to directly. These links were getting orphaned by the commit function because they were not updated. Now we also cycle through the links where this class is a target, fixing this problem. Of note here is the fact that we don't delete the links that exists from the parent to the original as we do just above; Doing so would kill all legit links to the original copy. Instead, we switch the links to the draft so they point to the original.
		$myTables=$this->relationTableNames("TARGET");
		foreach($myTables as $relTable){
			$this->q("update $relTable set targetRow=$originalID where targetRow=$draftID");
		}
		$this->writeMe();
		

		
		
		//lastly, clean up the old draft copy, which would otherwise be left behind.
		$this->readMe($draftID);
		if($this->id==0) new BB_Error("An error occurred during the commit attempting to initialize the draft copy before deletion.");
		self::mcdDelete("$this->tableName".$this->id);
		if(!$this->deleteMe()) new BB_Error("An error occured deleting the draft copy.");	

		
		$this->readMe($originalID); //get the commited data (the original, now refreshed) back in here. 			

		return true;
	}
	
		

	
	
	/**
	\brief Attempts to populate the class based on matching a field value in the database.
	
	Supplied with a column name and value, this function consults the database. If it finds an exact match it returns the id number of the row. 
	
	\param string $column The column to match with $value
	\param string $value
	
	\return unique key on success, false on failure
	*/
	public function match($column, $value){
		if($value=="") return false;
		$try=$this->q("select id from $this->tableName where $column='".mysql_real_escape_string($value)."'");
		if(mysql_num_rows($try)>0){
			$get=mysql_fetch_object($try);
			$this->readMe($get->id);
			return true;
		}
		return false;
	}

	/**
	\brief Returns an intuitive text label for displaying this object to humans
	
	This function should be overridden by inheritng functions. The override should provide get one or more non-null fields and concatenate them as needed to display some kind of human-identifiable and hopefully unique string identifying this resource in a list. 
	
	*/
	public function label(){
		$ret = $this->tableName;
		$ret="[$ret #$this->id]";
		return $ret;
	} 
	
	/**
	\brief Checks that the CMSUser has permissions to edit this kind of panel.
	
	May be overridden by inheriting classes. 
	
	\param mixed $user Either the ID number of a BB_CMSUser or a BB_CMSUser object
	
	returns true on success or a BB_Error object on failure
	
	*/
	public function canBeEditedBy($user){
		if(!property_exists($this,'isCopyOf')) return new BB_Error("No isCopyOf property in this class.");
		if(is_numeric($user)) $newU=new BB_CMSUser($user); else $newU=$user;
		$try=$newU->hasEditPermission($this->tableName,'ALL');
		if($try!==true) return new BB_Error("You don't have permission on that table.");
		return true;
	} 
	
	/**
	\brief Checks to see if there is another object of this type exists where isCopyOf is set to this objects ID
	
	\return true or false
	
	*/
	public function isBeingEdited(){
		if(!property_exists($this,'isCopyOf')) return false;
		$e=mysql_fetch_object($this->q("select count(id) as c from $this->tableName where isCopyOf=$this->id"));
		$copies=$e->c;
		if($copies>0) return true;
		return false;
	} 
	
	/**
	\brief Gets the name of the person editing this object or its draft copy.
	
	\return The name of the person editing this object or its draft copy.
	
	*/
	public function isBeingEditedByWho(){
		if(!property_exists($this,'isCopyOf')) return "Nobody";
		if($this->isCopyOf==0){
			$try=$this->q("select id from $this->tableName where isCopyOf=$this->id");
			if(!mysql_num_rows($try)){
				return "Nobody.";
			}
			$d=mysql_fetch_object($try);
			$temp=clone($this);
			$temp->readMe($d->id);
			if(!$temp->isAssignedTo) return("Administrator");
			return $temp->isAssignedTo->val('fullName');
		
		}else{ // this is a draft
			return $this->isAssignedTo->val('fullName');
		}
	}
	

	
	/**
	\brief Checks to see if the draft copy of this resource is assigned to the user
	
	For Resources, checks to see if this resource is unpublished and assigned to this user, and if there is, or there exists, a draft copy. For all other table types only checks to see if there's a draft.
	
	\return the id of the draft copy if it exists (and is assigned to the user if this is a Resource), or false
	
	*/
	public function isBeingEditedByMe(){
		global $curUser;
		if($this->id==0) return false;
		if(!property_exists($this,'isCopyOf')) return false;
		if($this->isCopyOf!=0 || ($this->tableName=='Resource' && $this->isPublished==0)) { //this is a draft copy. Is it assigned to user? (the Resource can have isCopyOf=0 and isAssignedTo!=0 when it's an unpublished draft.)
			if(property_exists($this,'isAssignedTo')){ 
					if($this->isAssignedTo->id == $curUser->id || $curUser->isAdmin()){ return $this->id; }else return false;
				}else return $this->id;
		}else{ //this is a committed panel. 
			$e=mysql_fetch_object($this->q("select id from $this->tableName where isCopyOf=$this->id"));
			if($e){ //There is a draft of it
				$newID=$e->id;
				$cName = "BB_".$this->tableName;
				$newClass= new $cName($newID);
				if(property_exists($newClass,'isAssignedTo')){ //is it assigned to user?
					if((is_object($newClass->isAssignedTo) && $newClass->isAssignedTo->id == $curUser->id) || $curUser->isAdmin()){ return $newID; }else { return false; }
				}else return $newID;
			}else return false;
		}
	}
	
	/**
	\brief creates a draft editing copy of this object.
	
	Uses existing mechanics : check to see if this user already has a draft copy. If so, return that one. Otherwise instantiate an identical copy of this one, then set its ID to 0 and its isCopyOf to the old ID and call writeMe which puts it into the database under a fresh ID number and creates the additional rows in the relation table. Then set isAssignedTo. 
	
	\return The object representing the new resource, or false on failure.
	
	*/
	public function createEditingCopy(){
		$cName="BB_".$this->tableName;
		$tid=$this->isBeingEditedByMe();
		if($tid){
			$this->readMe($tid);
			return($this);
		}else{
		global $curUser;
		if(empty($curUser)) new BB_Error("Fatal error: curUser not set when attempting to create editing copy of object $this->tableName $this->id");
		$nr=new $cName($this->id);
		if($nr->id==0) return false;
		if( $nr->isCopyOf==0 && $nr->tableName=='Resource' && $nr->isPublished==0) return($this); //exception for Resources which can be unpublished drafts that should not be duped.
		$nr->val('isCopyOf',$nr->id);
		$nr->val('id',0);
		if($this->tableName=='Resource') $nr->val('isPublished',0);
		if(!property_exists($this, "isAssignedTo")) { new BB_Error("Attempted to create a draft copy of type $this->tableName but object does not have appropriate property (isAssignedTo)"); return false; }
		$nr->isAssignedTo=$curUser;
		if($nr->writeMe()) return $nr; else return false; 
		}
	}
	/**
	\brief returns the name of the class linked to (or that should be linked to) in the field $fieldName. 
	
	\param string $fieldName the name of the property to consult
	
	\return false if the property is not a link field. Otherwise, the class name.
	*/
	public function linkedTableName($fieldName){
		if($fieldName=='') return false;
		$tt=$this->q("select targetTable from relationTypes where classVariable='".mysql_real_escape_string($fieldName)."'");
		if(!$tt) return false;
		if(mysql_num_rows($tt)==0) return false;
		$ttt = mysql_fetch_object($tt);
		return $ttt->targetTable;
	}
	
	
	
	
	/**
		\brief reassigns this object to another user
		
		\param mixed $user Either the ID of a BB_CMSUser or a BB_CMSUser object
		\param bool $cascade Is used internally to suppress errors on downstream objects; should not be set externally.
		
		This function now cascades through all the linked objects, attempting to reasssign them if they are assigned to the curent user. 
		
		\return true on success or false on fail
	
	*/
	public function reassignTo($user){
		if(func_num_args()==2) $cascade=func_get_arg(1); else $cascade=false;
		if(is_numeric($user)) $theUser=new BB_CMSUser($user); else $theUser=$user;
		if(!$theUser->id) { new BB_Error("CMSUser object invalid"); return false; }
		$try=$this->isBeingEditedByMe();
		if(!$try) { //This resource is not assigned to us, which is a problem if cascade is false as this is the Resource object (top of the chain).
			if(!$cascade) new BB_Error("Attempted to reassign an object that is not assigned to current user."); return false; 
			// otherwise, this is an object that is not assigned to us somewhere in the system so we do nothing here and just wait to cycle through the linked objects
		}else{ //This object is assigned to us somewhere in the system, but it's not necessarily $this. 
			if($this->id != $try){ //it's somewhere else, so we load it.
				$this->readMe($try);
			}
			if(!$this->id || (!$this->isCopyOf && $this->tableName!='Resource' && $this->val('isPublished')==1)) { new BB_Error("Error finding to draft object ID $try in reassignTo()."); return false; }
			//now we do the re-assign on this object
			if(property_exists($this,'isAssignedTo')) { 
				$this->isAssignedTo=$theUser;
				$this->writeMe();
			}
			
		}
		//Now we cycle through linked objects	
		$s=$this->q("select classVariable from relationTypes where sourceTable='$this->tableName'");
		while($sro=mysql_fetch_object($s)){
			$var=$this->{$sro->classVariable};
			if($var){
				if(!is_array($var)) $var=array($var);
				foreach($var as $theVar){
					if(method_exists($theVar,"reassignTo")) $theVar->reassignTo($theUser,true);
				}
			}
		}
		
		return true;
	
	}
	
	/**
	\brief returns an array of fieldnames where this object differs from $other
	
	\param obj $other the class to compare
	
	\return false if objects are the same, otherwise an array of fieldnames
	*/
	
	public function diff($other){
		if(get_class($this)!=get_class($other)) return false;
		
		$diffs=array();
		
		//first check database fields
		foreach($this->fieldList as $field){
			if($this->{$field}!=$other->{$field}) $diffs[]=$field;
		}
		//now the relations	
		$typeQ=$this->q("select * from relationTypes where sourceTable='$this->tableName'");
		while($typeRow=mysql_fetch_assoc($typeQ)){
			$varName=$typeRow['classVariable'];
			if(empty($this->{$varName})) {
				if(!empty($other->{$varName})) $diffs[]=$varName;
			}elseif(is_object($this->{$varName})){
				if($this->{$varName}->id != $other->{$varName}->id || $this->{$varName}->diff($other->{$varName}) ) $diffs[]=$varName;
			}elseif(is_numeric($this->{$varName})){
				if($this->{$varName} != $other->{$varName}) $diffs[]=$varName;
			}elseif(is_array($this->{$varName})){
				$thisArray=$this->{$varName};
				$otherArray=$other->{$varName};
				if(count($thisArray)!=count($otherArray)) $diffs[]=$varName; 
				for($i=0;$i<count($thisArray);$i++){
					if(is_numeric($thisArray[$i])){ //array of ints
						if($thisArray[$i] != $otherArray[$i]) $diffs[]=$varName;
					}elseif(is_object($thisArray[$i])){ // array of class objects
						if($thisArray[$i]->id != $otherArray[$i]->id || $thisArray[$i]->diff($otherArray[$i]) ) $diffs[]=$varName;
					
					}
					
				}
			}else new BB_Error("Error comparing values in diff() - could not interpret type for $varName");
			
		}
		if(count($diffs)) return $diffs; else return false;
	}
	
	
	/**
	\brief returns an associative array representing the object for use on the front end
	
	We'll may want to offer collaborators access to database objects without also exposing methods that change database info. Note that there's a dedicated BB_LocalListing class which exposes the Resource object and it's children with appropriate processing that should be used for display purposes.
	
	\return An nested associative array representing the object properties.
	*/
	
	public function toArray(){
		$retArr=array();
		
		$sql="select classVariable, multiple from relationTypes where sourceTable='$this->tableName'";
		$m=$this->q($sql);
		$linkFields=array();
		$multis=array();
		while($mc=mysql_fetch_object($m)){
			$linkFields[]=$mc->classVariable;
			if($mc->multiple=='1') $multis[$mc->classVariable]=true;
		}
		
		$sql="select * from formDefinitions where view='CMS' and class='BB_$this->tableName' order by ordinal";
		$m=$this->q($sql);
		while($mc=mysql_fetch_object($m)){
			if(!in_array($mc->variable, $this->fieldList)) { //this is a linked field 
				if($multis[$mc->variable]){ //this is an array of linked fields
					$retArr[$mc->variable]=array();
					foreach($this->{$mc->variable} as $theOb){
						$retArr[$mc->variable][]=$theOb->toArray();
					}
				}else{ //this object is stored directly in the variable slot
					if(is_object($this->{$mc->variable}))
						$retArr[$mc->variable]=$this->{$mc->variable}->toArray();
					else
						$retArr[$mc->variable]='';
				}
			}else{ //this field is in the object
				$retArr[$mc->variable]=$this->val($mc->variable);
			}
		}	
		return $retArr;	
	}



	/**
	\brief Uses the relations tables to attempt to locate a Resource object that links to the current object. 
	
	\param $useCommitted Variable that forces the recursion to also pass from draft copies to their committed versions.
	\param $maxIterations The cap on the number of joins to do while looking for a resource. Used to prevent infinite loops. Defaults to 5.
	
	\returns A BB_Resource object on success or false on failure.
	
	*/
	
	public function getParentResource($useCommitted=false){
		// Procedure: 1) get an array of fields from relationTypes that have this object as a target.
		// 2) iterate, get parents. If sourcetable is Resource, initialize and return the parent object
		// 3) if sourcetable is not resource and iterations > 0, intialize objects and call getParentResource on them with n-1 maxIterations 
		// 4) return new method value
		// 5) if we still have nothing and useCommitted is true, we try the committed version 
		if(func_num_args()==2) $maxIterations=func_get_arg(1); else $maxIterations=5;

		if($this->tableName=='Resource') return $this->id;
		for($i=0;$i<count(self::$relationTypesCache);$i++){
//		$myTables=$this->q("select * from relationTypes where targetTable='$this->tableName'");
//		while($row=mysql_fetch_object($myTables)){
			$row=self::$relationTypesCache[$i];
			if($row['targetTable']!=$this->tableName) continue;
			$try=$this->q("select sourceRow from rel_".$row['type']." where targetRow=$this->id");
			while($r=mysql_fetch_object($try)){
				 if($row['sourceTable']=='Resource'){
				 	return(new BB_Resource($r->sourceRow)); 
				 }else{ 
				 	$cName="BB_".$row['sourceTable'];
				 	$tab=new $cName($r->sourceRow);
				 	$testRes = $tab->getParentResource($maxIterations-1);
				 	if($testRes) return $testRes;
				 }
			}
		}
		// at this point if we still haven't returned a value then this object's probably a draft copy so we will pass to that committed copy and continue.
		
		if($this->isCopyOf!=0 && $useCommitted){
			$nob=clone($this);
			$nob->readMe($this->isCopyOf);
			$testRes = $nob->getParentResource($maxIterations);
			if($testRes) return $testRes;
		}
		return false;
	}	
	/**
	\brief Uses the relations tables to attempt to locate a Resource object that links to the current object and return an array of objects between this one and the Resource 
	
	\param $maxIterations The cap on the number of joins to do while looking for a resource. Used to prevent infinite loops. Defaults to 5.
	
	\returns An array of objects on success or false on failure.
	
	*/
	
	public function getEditPathToResource($targetRes, $maxIterations=5, $retA){
	
	if(!isset($retA)) $retA=array(array($this->tableName."|".$this->id."|ME"));
		// Procedure: 1) get an array of fields from relationTypes that have this object as a target.
		// 2) iterate, add to the array . If sourcetable is Resource,return array
		// 3) if sourcetable is not resource and iterations > 0, intialize submit array to recursion
		// 4) return new method value
		if($maxIterations==0) return(end($retA));

		$newRetVal=array();
		foreach($retA as $ar){	
			//echo("<br/>processing ".print_r($ar,true));
			$temp=explode("|",end($ar));
			$curTable= $temp[0];
			$curID= $temp[1];
			if($curTable=='Resource') return $ar;
			$myTables=$this->q("select * from relationTypes where targetTable='$curTable'");  //echo(("<br/>select * from relationTypes where targetTable='$curTable'"));
			$parentsFound=0;
			while($row=mysql_fetch_object($myTables)){ 
				$try=$this->q("select sourceRow from rel_$row->type where targetRow=$curID"); //echo("<br/>select sourceRow from rel_$row->type where targetRow=$curID");
				if(mysql_num_rows($try)>0){ //We found a parent class
					while($r=mysql_fetch_object($try)){
						$parentsFound++;
						$proposed=$ar;
						$proposed[]="$row->sourceTable|$r->sourceRow|$row->type";
						$newRetVal[]=$proposed;
						//echo("<br/> added "."$row->sourceTable|$r->sourceRow|$row->type result in ".print_r($proposed,true));
						 if($row->sourceTable=='Resource' && $r->sourceRow==$targetRes){
						 	return(end($newRetVal)); 
						 }
					}
				}
			}
			if(!$parentsFound){ //There was no parent; this is a draft copy of some subpanel which is not linked to from a parent we'll add a special element to the array.
				$newID=$this->q("select isCopyOf from $curTable where id=$curID"); //echo("<br/>".("select isCopyOf from $curTable where id=$curID"));
				if(mysql_num_rows($newID)>0){
					$n=mysql_fetch_object($newID);
					$proposed=$ar;
					$proposed[]=$curTable."|$n->isCopyOf|DRAFT";
					//echo("<br/> added "."$curTable|$n->isCopyOf|DRAFT result in ".print_r($proposed,true));
					$newRetVal[]=$proposed;
				}
			}
			
		}
		
		return $this->getEditPathToResource($targetRes,$maxIterations-1,$newRetVal);
	}
	
	
	/**
	\brief A cleanup function for draft copies.
	
	This function will only work on resources that are draft copies assigned to the $curUser. It mimics the structure of the reassignTo function in that it cascades down to other draft copies of stuff that are assigned to this user, which are also deleted.
	
	
	*/
	
	public function cascadingDraftDelete(){
		global $curUser;
		
		if(func_num_args()==1) $cascade=func_get_arg(0); else $cascade=false;

		if(!$curUser->id) { return (new BB_Error("curUser object invalid"));  }
		
		$doDelete=false;

		$try=$this->isBeingEditedByMe();
		if(!$try) { //This resource is not assigned to us, which is a problem if cascade is false as this is the Resource object (top of the chain).
			if(!$cascade) return( new BB_Error("Attempted to run cascadingDraftDelete on an object that is not assigned to current user.")); 
			// otherwise, this is an object that is not assigned to us somewhere in the system so we do nothing here and just wait to cycle through the linked objects
		}else{ //This object is assigned to us somewhere in the system, but it's not necessarily $this. 
			if($this->id != $try){ //it's somewhere else, so we load it.
				$this->readMe($try);
			}
			if(!$this->id || (!$this->isCopyOf && $this->tableName!="Resource")) { return(new BB_Error("Error reading the draft object ID $try in cascadingDraftDelete() after system determined there was one."));  }
			//now we authorize the delete on this object
			if(property_exists($this,'isAssignedTo') && $this->isAssignedTo && $this->isAssignedTo->id==$curUser->id) { 
				$doDelete=true;
			}
			
		}
		
		//Now we cycle through linked objects	
		$s=$this->q("select classVariable from relationTypes where sourceTable='$this->tableName'");
		while($sro=mysql_fetch_object($s)){
			$var=$this->{$sro->classVariable};
			if($var){
				if(!is_array($var)) $var=array($var);
				foreach($var as $theVar){
					if(method_exists($theVar,"cascadingDraftDelete")) { 
						$try=$theVar->cascadingDraftDelete(true);
						if($try instanceof BB_Error) return $try;
					}
				}
			}
		}
		
		//OK the child objects have been deleted; we can do this one if it passed the checks above
		// We set force delete to 'true' because we don't want to check permissions. 
		if($doDelete) return( $this->deleteMe(true) ); else return true;
	
	}
	
	
	
	/**
	\brief A function called by the publish code to make sure that child object draft copies assigned to curUser are also committed before publish
	
	\remark $curUser should be reset to the isAssignedTo property of the Resource in question by any script using this function so that permissions are checked effectively.
	
	\return true on success, BB_Error on fail.
	*/
	
	public function cascadingCommitChildObjects(){
		global $curUser;

		if(func_num_args()==1) $cascade=func_get_arg(0); else $cascade=false;

		if(!$curUser->id) { return (new BB_Error("Invalid user passed to cascading Commit function"));  }
		
		

			$try=$this->isBeingEditedByMe();
			if(!$try) { //This resource is not assigned to us, which is a problem if cascade is false as this is the Resource object (top of the chain).
				if(!$cascade) return( new BB_Error("Attempted to run cascadingCommitChildObjects on an object that is not assigned to user ID #$curUser->id.")); 
				// otherwise, this is an object that is not assigned to us somewhere in the system so we do nothing here and just wait to cycle through the linked objects
			}else{ //This object is assigned to us somewhere in the system, but it's not necessarily $this. 
				if($this->id != $try){ //it's somewhere else, so we load it.
					$this->readMe($try);
					
				}
				//now we try the commit, failing silently if condition is not met, so as not to interrupt the function if an object being committed (and thus, changing ID number) is linked to more than once by the same parent object.
				if($this->id && ($this->isCopyOf || ($this->tableName=='Resource' && $this->isPublished==0)) && property_exists($this,'isAssignedTo')) { 
					if($cascade) $this->commitChanges(); //we don't commit the first object in the recursion because it's the Resource and we will be using publishMe() for that.
				}
				
			}

		//Now we cycle through linked objects	
		$s=$this->q("select classVariable from relationTypes where sourceTable='$this->tableName'");
		while($sro=mysql_fetch_object($s)){
			$var=@$this->{$sro->classVariable};
			if($var){
				if(!is_array($var)) $var=array($var);
				foreach($var as $theVar){
					if(method_exists($theVar,"cascadingCommitChildObjects")) { 
						$try=$theVar->cascadingCommitChildObjects(true);
						if($try instanceof BB_Error) return $try;
					}
				}
			}
		}
		
		return true;
	}
	
	/** This function goes through the linked objects and, if there exists a draft copy of that object assigned to this user, substitutes that copy for the object. 
	
	This function is called by the publish checklist so that the checks are carried out on the Resource <strong>as it will exist</strong> after being published.
	

	*/
	
	public function loadDraftsOfLinkedObjects(){
		
		for($i=0;$i<count(self::$relationTypesCache);$i++){
			$relType=self::$relationTypesCache[$i];
			if($relType['sourceTable']==$this->tableName){
				//echo("<br/>checking $this->tableName ".$relType['classVariable']);
				if($this->{$relType['classVariable']}){
					
					if(is_object($this->{$relType['classVariable']})) {
						$theObject=$this->{$relType['classVariable']};
						if(method_exists($theObject, "isBeingEditedByMe")) $try=$theObject->isBeingEditedByMe(); else $try=false;
						if($try) $theObject->readMe($try);
						if(method_exists($theObject, "loadDraftsOfLinkedObjects")) $theObject->loadDraftsOfLinkedObjects();
					}elseif(is_array($this->{$relType['classVariable']})){
						foreach($this->{$relType['classVariable']} as $theObject){
							if(method_exists($theObject, "isBeingEditedByMe")) $try=$theObject->isBeingEditedByMe(); else $try=false;
							if($try) $theObject->readMe($try);
							if(method_exists($theObject, "loadDraftsOfLinkedObjects")) $theObject->loadDraftsOfLinkedObjects();						
						}
					}
					
				}
			}
		}
	
	}
	
	/**
		
	
	*/
	
	
}



?>
