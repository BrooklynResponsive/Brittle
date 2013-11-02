<?php

/** 
	\brief Represents an image 
	
	

*/
class BB_Image extends BB_DB_Obj{

	public $isCopyOf;
	public $captionHeader;
	public $caption;
	public $credit;
	public $path;
	public $thumbPath;
	public $dateUploaded;
	
	
	public $tags;
	public $suggestedTags; //used by the story tagger to store suggestions before confirmation/processing by humans.
	public $relatedStoryIDs; 
	public $gallerySectionTag; //used in story templates to organize the top 100 stuff, etc.
	
	
	/// Holder for the CMSUser to which this resource is assigned, if it is being edited
	public $isAssignedTo;	
	
	
	public function BB_Image($initArray=false){
		$fieldList=array('isCopyOf','captionHeader','caption','credit','path','thumbPath','dateUploaded');
		parent::__construct('Image', $fieldList, $initArray);
	} 
		
			

	public function label(){
		return BB_IO::coalesce(BB_IO::xWords($this->caption,8,true), end(explode("/",$this->path)));
	}
	
	
	/**
	\brief returns a string of text in this object.
	
	\param int $importance the importance level of the text to get. Currently we provide two levels, of which Image only returns for low-importance
	
	
	*/
	public function getFulltext($importance){

		if($importance>0) return($this->captionHeader." ".$this->caption);

	
	}
	
	
	/**
	\brief Pulls an uploaded image from the POST array. 
	
	\param string $inputName The name of the file array in POST to check. you can also submit an array index as part of the string, <code>"myfilename[$i]"</code>
	\param string $myFilename The name under which to save the file
	\param string $pathExtension Additional path elements to append between the directory root and the filename
	\param int $maxWidth The largest width to accept, in pixels
	\param int $maxHeight The largest height to accept, in pixels
	\param bool $resizeToMax when true and the image is larger than $maxWidth x $maxHeight, the image is scaled up to the maximum dimensions.
	\param bool $padWithWhiteSpace when this and $resizeToMax are true and the image is smaller than $height x $width, the image is not resized but padded out with whitespace to reach the desired dimensions.
	
	\return An integer representing the error code:
			<ul>
				<li>[1] Success</li>
				<li>[2] Name not specified or file data not found</li>
				<li>[3] File data not found</li>
				<li>[4] Internal file upload error</li>
				<li>[5] Maximum upload size (2MB) exceeded</li>
				<li>[6] Wrong image type (JPEG and GIF only)</li>
				<li>[7] File name already used</li>
				<li>[8] Error moving temp file to image directory </li>
				<li>[9] File name already used</li>
				<li>[10] Error resizing image</li>
				<li>[11] Permission denied</li>
				<li>[12] Unknown extension</li>
			</ul>
			
			
			
	*/
	public function newFromPost($inputName, $myFilename, $pathExtension='',$maxWidth=MAX_IMAGE_WIDTH,$maxHeight=MAX_IMAGE_HEIGHT, $resizeToMax=true, $padWithWhiteSpace=true){
		//if($maxWidth>MAX_IMAGE_WIDTH) $maxWidth=MAX_IMAGE_WIDTH;
		//if($maxHeight>MAX_IMAGE_HEIGHT) $maxHeight=MAX_IMAGE_HEIGHT;

		if($inputName=="") return(2);
		
		if(substr($inputName,-1)=="]"){ //if array index submitted
			$try = preg_match("/\[([0-9]+)\]/",$inputName, $matches );
			if(!$try) return(3);
			$fileIndex=$matches[1];
			$temp=explode("[",$inputName);
			$inputName=$temp[0];
			$ia=$_FILES[$inputName];
			if(!is_array($ia)) { return(3); }
			$origname=$ia['name'][$fileIndex];
			$type=$ia['type'][$fileIndex];
			$tmp_name=$ia['tmp_name'][$fileIndex];
			$error=$ia['error'][$fileIndex];
			$size=$ia['size'][$fileIndex];
		}else{
			$ia=$_FILES[$inputName];
			if(!is_array($ia)) return(3);
			$origname=$ia['name'];
			$type=$ia['type'];
			$tmp_name=$ia['tmp_name'];
			$error=$ia['error'];
			$size=$ia['size'];
		}
		

		
		$file_ext=end(explode(".",$origname));
		if($error!=0){ return(4); }
		if($size > MAX_IMAGE_SIZE_ACCEPTED) return(5);
		//if(!in_array($type,array('image/jpeg','image/gif'))) return(6);
		if(!in_array(strtolower($file_ext),array('gif','jpeg','jpg'))) return(6);
		
		$myFilename=str_ireplace(".jpg","",$myFilename);
		$myFilename=str_ireplace(".gif","",$myFilename);
		$myFilename=str_ireplace(".jpeg","",$myFilename);
		$myFilename.=".".$file_ext;
		
		if($pathExtension!=""){ //just some safeguards first
			if(substr($pathExtension,0,1)=="/") $pathExtension=substr($pathExtension,1);
			if(substr($pathExtension,-1)=="/") $pathExtension=substr($pathExtension,0,-1);
			$pathTemp=explode("/",$pathExtension);
			array_push($pathTemp, $myFilename);
			$myFilename=implode("/",$pathTemp);
		}
		

		$relPath=$myFilename;


		//What follows ensures a unique filename if the proposed one is already taken.
		$newPath=$relPath;
		$imgC=0;
		while(file_exists(DOCUMENT_ROOT."/".$newPath)){
			$imgC++;
			$t=explode(".",$newPath);
			$ext=array_pop($t);
			$newPath=implode(".",$t);
			if(is_numeric(end(explode("_", $newPath)))){
			
				$tw=explode("_",$newPath);
				array_pop($tw);
				$newPath=implode("_",$tw)."_$imgC";
			
			}else{
			
				$newPath.="_$imgC";
			
			}
			$newPath.=".".$ext;
		}
		
		$relPath=$newPath;
		$absPath=DOCUMENT_ROOT."/".$relPath;
		
		
		
		list($width_orig, $height_orig) = getimagesize($tmp_name);
		
		if (($width_orig == '') || ($height_orig == '')) { //Some uploaded JPEGs have corrupt EXIF data and getimagesize just return false instead of an information array
			return(13);
		}

		if(($width_orig==$maxWidth && $height_orig==$maxHeight) || (!$resizeToMax && $width_orig<=$maxWidth && $height_orig<=$maxHeight)){ //don't need to resize
			$move=move_uploaded_file($tmp_name,$absPath);
			if(!$move) return(8);
			$this->path=$relPath;
			return(1);
		}
		
	
		$image=$this->resizeTo($tmp_name,$file_ext,$maxWidth,$maxHeight, $padWithWhiteSpace);
		if($image===false) return(10);
		switch(strtolower($file_ext)){
			case "gif":
				if(!@imagegif($image, $absPath)){
					return(11);
				}
			break;
			case "jpg":
			case "jpeg":
				if(!@imagejpeg($image, $absPath)){
					return(11);
				}
			break;
			default:
				return(12);
			break;
		}
		$this->path=$relPath;


		return(1);		
	}
	
	/**
	\brief Makes sure the upload date is set.
	*/	
	
	protected function initForFirstWrite(){
		if(!isset($this->dateUploaded)) $this->dateUploaded=$this->SQLTimeNow();
	}
		
		
	/**
	\brief Makes sure a thumb is generated from this image on save, if there is a path.
	
	*/	
/*
	public function writeMe(){
		if($this->path!="");
		$thisPath="/images/thumbs".self::normalizePathWebRoot($this->path);
		if(!file_exists(DOCUMENT_ROOT.$thisPath)) self::makeThumb($thisPath);
			
		return parent::writeMe();
	}
*/
		
		
	/**
	\brief Returns a string representing a human-readable version of the errorCode result from newFromPost()
	
	\param int $error the error code returned by newFromPost()
	
	\return A string representing a human-readable version of the errorCode result from newFromPost()
	*/
	public function newFromPostHumanReadableError($error){
		$error=(int)$error;
		$errors=array("Error code not recognized","Success","Name not specified or file data not found","File data not found","Internal file upload error","Maximum upload size (2MB) exceeded","Wrong image type (JPEG and GIF only)","File name already used, try changing your description","Error moving temp file to image directory ","File name already used, try changing your description","Error resizing image","Permission denied","Unknown extension", "Corrupt image EXIF data found. Please try creating a new copy using Photoshop.");
		return($errors[$error]);
	
	}
	
	
	
	/**
	\brief Resizes an image to fit in a box $width wide and $height tall and returns the image object. The image is centered and optionally padded with whitespace.
	
	\param string $imageFile The filename of the image to examine
	\param string $ext The extension (file type) to force, GIF or JPG.
	\param int $width The width to resize to
	\param int $height The height to resize to
	\param bool $padWithWhiteSpace when true and the image is smaller than $height x $width, the image is not resized but padded out with whitespace to reach the desired dimensions.
	
	\returns an image result from imagecreatetruecolor() of the new size
	
	
	*/
	public function resizeTo($imageFile,$ext='',$width, $height, $padWithWhiteSpace=true){ //
		if(filesize($imageFile)>MAX_IMAGE_SIZE_ACCEPTED) return false; //avoids out of memory failures
		if($ext==='') $ext=end(explode(".",$imageFile));
		list($widthO, $heightO) = getimagesize($imageFile);

		
		//get image in question from file. 
		switch(strtolower($ext)){
			case "gif":
				$image = @imagecreatefromgif($imageFile);
			break;
			case "jpg":
			case "jpeg":
				$image = @imagecreatefromjpeg($imageFile);
			break;
		}
		//Now resize and center.
		// get ratios
		$heightRatio = $height/$heightO;
		$widthRatio = $width/$widthO;
		//We will multiply both by the smaller of the two, so determine that
		$ratio = ($heightRatio > $widthRatio) ? $widthRatio : $heightRatio;
		// scale the width and height
		$newHeight=$ratio * $heightO;
		$newWidth=$ratio * $widthO;
		
		




		//create destination image
		// if pad with whitespace is true, the resulting image will always be $width by $height.
		if($padWithWhiteSpace){
			$image_p = imagecreatetruecolor($width, $height);
			$white = imagecolorallocate($image_p, 255, 255, 255);
			imagefill($image_p, 0, 0, $white);
			//newX and newY are offsets equaling half of the difference between the dimension and the maximum, since this image will be centered on the white background
			$newX=round(($width-$newWidth)/2);
			$newY=round(($height-$newHeight)/2);

		}else{ //if pad with whitespace is false, the thumb will have the same proportions as the input image
			$image_p = imagecreatetruecolor($newWidth, $newHeight);
			$newX = 0;
			$newY = 0;
		}
		
		

		if(imagecopyresampled( $image_p, $image, $newX,$newY,0,0, $newWidth, $newHeight, $widthO, $heightO )) return($image_p); else return(false);
	}


	/**
	 Images come in all shapes and sizes. This function outputs an img element with an E7E7E7 border (unless $grayBorder=false) with appropriate style information such that it takes up exactly maxWidth x maxHeight pixels. The image will be expanded to fit the tightest of those dimensions if $expand = false
	 
	 \param string $path the path to the image. This can be relative to the web or document root, with or without leading slash
	 \param int $maxWidth The maximum width of the image itself, and the final dimension of the img element with margins
	 \param int $maxHeight The maximum height of the image itself, and the final dimension of the img element with margins 
	 \param bool $expand Whether the image should be expanded so at least one of its dimensions equals the maxWidth or maxHeight
	 \param string $alt Optional, the alt attribute text to use
	 \param string $class Optional, one or more class declarations
	 \param string $style Optional, extra stuff for the style attribute
	 \param mixed $grayBorder Controls whether the gray border is displayed. If (bool)true then border is shown on all 4 sides. Can also be a CSS-style declaration of "top/bottom left/right" where 0 is off and 1 is on. example: "0 1" to show the border only on the left/right sides
	 
	
	*/
	static function sizedIMG($path, $maxWidth, $maxHeight, $expand=false, $alt=false, $class=false, $style=false, $grayBorder=true){

	
	if($maxWidth < MAX_THUMB_IMAGE_WIDTH && $maxHeight < MAX_THUMB_IMAGE_HEIGHT) $useThumb=true; else $useThumb=false;
	//sometimes the path supplied already indicates a thumb:
	if(strpos($path,"/images/thumbs")!==false) $useThumb=false; 


	//store dimensions for enclosing box
	if($grayBorder){
		if(is_bool($grayBorder)){
			$maxHeight-=2; //to account for the border
			$maxWidth-=2; //to account for the border
			$borderTB=1;
			$borderLR=1;
		}
		if(is_string($grayBorder)){
			$t=explode(" ",$grayBorder);
			$borderTB=(int)$t[0];
			$borderLR=(int)$t[1];
			if($borderTB) $maxHeight-=2;
			if($borderLR) $maxWidth-=2;
		}
	}
	
	
	$path=self::normalizePathWebRoot($path);
	$realpath=DOCUMENT_ROOT.$path;
	

	
	
	if(!file_exists($realpath) && strpos($realpath,"/images/thumbs/")!==false) self::makeThumb($realpath); //if we can't find the image
	if(!file_exists($realpath) || $realpath == DOCUMENT_ROOT."/") { $realpath=DOCUMENT_ROOT.UNAVAILABLE_IMAGE_PATH; $path=UNAVAILABLE_IMAGE_PATH; }
	if($useThumb){ //if this is a small image we need to use the thumb, and do our calculation on the basis of the smaller thumb image (which may have different dimensions owing to whitespace padding) 
		$realpath=DOCUMENT_ROOT."/images/thumbs".$path;
		$relativePath="/images/thumbs".$path;
	}else{
		$relativePath=$path;
	}
	
	
	// Now get the actual dimensions.
	$idata=getimagesize($realpath);
	$realw=$idata[0];
	$realh=$idata[1];
	//echo("<!-- w $maxWidth : h  $maxHeight-->");
	
	if($realw/$realh > 700/467 ){
		//image is too wide 
		$ratio=$maxWidth/$realw;
		
	}elseif($realw/$realh <= 700/467 ){
		//image is too tall or OK
		$ratio=$maxHeight/$realh;
	}

	// Now resize, if necessary
	if($expand || $realw > $maxWidth || $realh > $maxHeight){
		$realw=round($realw*$ratio);
		$realh=round($realh*$ratio);
		if($realw>$maxWidth) $realw=$maxWidth;
		if($realh>$maxHeight) $realh=$maxHeight;
	}

	// Now calculate padding necessary;
	$paddingTB = round(($maxHeight - $realh)/2);
	$paddingLR = round(($maxWidth - $realw)/2);
	if($paddingTB<0) $paddingTB=0;
	if($paddingLR<0) $paddingLR=0;
	if($paddingTB<3 && $borderTB) $paddingTB=0;
	if($paddingLR<3 && $borderLR) $paddingLR=0;
	
	
	
	$w="width:$realw"."px;";
	$h="height:$realh"."px;";
	$m="padding:$paddingTB"."px $paddingLR"."px;";
	if($borderTB && $borderLR) $bclass='gborder';
	elseif($borderTB) $bclass='gborder-TB';
	elseif($borderLR) $bclass='gborder-LR';
	$c=($class)?"class='$class $bclass'":"class='$bclass'";
	
	
	?>
		<img src='<?=CDN_HOST.$relativePath;?>' <?=$c;?> style="<?=$w.$h.$m;?><?=($style)?$style:"";?>" alt="<?=BB_IO::outForCode($alt);?>"  />

	<?
	
	}
	
	/**
	 \brief A replacement for BB_Image::sizedIMG for responsive designs.
	 
	 	Differences from BB_Image::sizedIMG
	 	1) this function does not output a height in the style attribute. 
	 	2) units can be given in px (assumed) or in ems. If given in ems, the width in ems is output directly and no resizing is done.
	 	3) maxWidth can be zero, in which case the image is output without a width declaration.
	 	3a) If the old $expand parameter is true, a width=100% is added to the style attribute.
	 	4) maxWidth can be 'THUMB' in which case a thumb of the image is output without a width declaration.
	
	*/
	static function sizedIMGResponsive($path, $maxWidth=0, $alt=false, $expand=false){
		$EMS = (stripos($maxWidth,"em")!==false); //whether we are using ems
		$omitWidth=false;
		if($EMS){
			$maxWidth=(float)(trim(str_ireplace("em","",$maxWidth)));
	
			
			//16 ain't really perfect for the em-to-px conversion but it'll do for us.
			if($maxWidth < (MAX_THUMB_IMAGE_WIDTH/15)) $useThumb=true; else $useThumb=false;
		}else{
			if($maxWidth === 'THUMB'){ $useThumb = true; $omitWidth = true; }
			elseif($maxWidth===0){ $useThumb = false; $omitWidth = true; }
			elseif($maxWidth < MAX_THUMB_IMAGE_WIDTH) $useThumb=true; else $useThumb=false;
		}
	
		
		//sometimes the path supplied already indicates a thumb:
		if(strpos($path,"/images/thumbs")!==false) $useThumb=false; 
	
	
		
		$path=self::normalizePathWebRoot($path);
		$realpath=DOCUMENT_ROOT.$path;
		
		
		if(!file_exists($realpath) && strpos($realpath,"/images/thumbs/")!==false) self::makeThumb($realpath); //if we can't find the image
		if(!file_exists($realpath) || $realpath == DOCUMENT_ROOT."/") { $realpath=DOCUMENT_ROOT.UNAVAILABLE_IMAGE_PATH; $path=UNAVAILABLE_IMAGE_PATH; }
		if($useThumb){ //if this is a small image we need to use the thumb, and do our calculation on the basis of the smaller thumb image (which may have different dimensions owing to whitespace padding) 
			$realpath=DOCUMENT_ROOT."/images/thumbs".$path;
			$relativePath="/images/thumbs".$path;
		}else{
			$relativePath=$path;
		}
		
		
		// Now get the actual dimensions.
		$idata=getimagesize($realpath);
		$realw=$idata[0];
		$realh=$idata[1];
		//echo("<!-- w $maxWidth : h  $maxHeight-->");
		
		if($realw/$realh > 700*(2/3) ){
			//image is too wide 
			$ratio=$maxWidth/$realw;
			
		}elseif($realw/$realh <= 700*(2/3) ){
			//image is too tall or OK
			$ratio=1;
		}
	
		if(!$EMS){
					// Now resize, if necessary
					if($realw > $maxWidth){
						$realw=round($realw*$ratio);
						$realh=round($realh*$ratio);
						if($realw>$maxWidth) $realw=$maxWidth;
						if($realh>$maxHeight) $realh=$maxHeight;
					}
				
					if($omitWidth) $w=""; else $w="style='width:{$realw}px;'";
					if($expand) $w="style='width:100%'";
		}else{
			$w = "style='width:{$maxWidth}em;'";
		}
		
		
		
		
		?>
			<img src='<?=CDN_HOST.$relativePath;?>' <?=$w;?> alt="<?=BB_IO::outForCode(strip_tags($alt));?>"  />
	
		<?
	
	}
	
	/**
	\brief Normalizes an image path relative to the web root.
	
	*/
	static function normalizePathWebRoot($path){
		// first process path into realpath
		if(substr($path,0,1)!="/") $path="/$path";
		if(stripos($path,DOCUMENT_ROOT)!==false) {
			$path=str_ireplace(DOCUMENT_ROOT, "", $path);
		}
		if(substr($path,0,12)=='/mediafiles/'){ //certain older images begin with 'mediafiles' but that directory is in content/editorial
			$path="/content/editorial".$path;
		}
		
		return $path;
	}
	
	/**
	\brief Returns getimagesize() for this image.
	
	*/
	public function imagesize(){
		return getimagesize(DOCUMENT_ROOT.self::normalizePathWebRoot($this->path));	
	}

	
	/**
	 \brief Assuming path begins with /images/thumbs, removes those elements of the path to find the big image and creates a thumbnail to exist at the path given.
	  Called in thumbgenerator.php and BB_Image::sizedIMG 
	  
	  
	*/
	static function makeThumb($path){
		$path=str_replace(DOCUMENT_ROOT,"",$path);
		if(strpos($path,"/images/thumbs/")!==0) return new BB_Error("Path passed to makeThumb must start with /images/thumbs");
		
		$realPath=DOCUMENT_ROOT.str_replace("/images/thumbs","",$path);

	
		if(!file_exists($realPath)) return;
		$ext=strtolower(end(explode(".",$realPath)));
		
		//resize the image
		$I=new BB_Image();
		$newImage=$I->resizeTo($realPath,$ext,MAX_THUMB_IMAGE_WIDTH, MAX_THUMB_IMAGE_HEIGHT, false); //
	
		//make sure the path exists	
		$directories=explode("/",$path);
		array_pop($directories);
		$newdir=DOCUMENT_ROOT.implode("/",$directories);	
		if(!is_dir($newdir)) {
			$try=mkdir($newdir, 0775, true);
			if(!$try) exit(); 
		}
		 
		
		if($ext=="jpg" || $ext=="jpeg"){
			imagejpeg($newImage,DOCUMENT_ROOT.$path,75);			
		}else if($ext=="gif" || $ext=="GIF"){
			imagegif($newImage,DOCUMENT_ROOT.$path,75);	
		}
	
	}
	
	
	
}


?>