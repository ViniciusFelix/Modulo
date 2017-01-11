<?php
/*
 * Class FormValidator.php
 * This class is created to automate the process of form validation. The class 
 * has methods to validate number, blank entries, email etc. This only need a 
 * proper naming convention for form elements. You have instantiate this class 
 * and call a method to do the validation with posted value array.
 * 
 * e.g. validation of not blank form element
 * 
 * Html form
 * ---------
 * 
 * User Name &lt;input type = "text" name ="t_user_name_1"&gt;
 * &lt;input type = "submit" name ="b_submit_0" value="Submit"&gt;
 * 
 * Php file
 * --------
 * &lt;?php
 * require_once("./FormValidator.class.php");
 * $fv = new FormValidator($_POST);
 * $fv-&gt;doValidation();
 * if($fv-&gt;isError()) {
 * 		echo($fv-&gt;getErrorBox());
 * }
 * ?&gt;
 * Result in case of no input is given in the form -
 * 					- User Name Can not be blank
 * You have set flag with exact label name you want to show in error message. If we break the name of text box, this will become -
 * 					
 * 					t user name 1
 * 
 * t - is a flag stands for text box
 * user name - will be used in the error message in case of any error as "User Name"
 * and
 * 1 - is a flag stands for validation for not blank entry.
 * 
 * You can figure out various flag by following list.
 *
 * Type
 * ----
 *		t : Textbox
 *		p : Password
 *		s : Select
 *		b : Button
 *		c : Checkbox
 *		r : Radio
 *		f : File
 *		i : Image
 *		x : Text Area
 *		h : hidden
 *		d : fieldset
 *
 * Validation Flag
 * ---------------
 *		0 : need no validation
 *		1 : validation of blank
 *		2 : not blank e-mail
 *		3 : optional e-mail
 *		4 : not blank date
 *		5 : optional date
 *		6 : not blank numeric 
 *		7 : optional numeric
 *
 * if you have any query or question feel free to contact at pawan269@hotmail.com 
 */
 
//////////////////////////////////////////////////////////////////////
// Main Code that will get all the form field Elements 				//
//////////////////////////////////////////////////////////////////////

/**
 * FormValidator is created to automate the lengthy process of form validation,
 * what you have to do is to just provide the correct naming for your form 
 * elements and this class will do the validation for you.
 * 
 * - @link FormValidator($_POST) : Object Constructor
 * - @link getErrorBox() : return error box having error messages
 * - @link getErrorMessage() : returns error messages only
 * - @link setErrorMessage($Message): sets an error message string to end output
 * - @link isError() :  returns true in case of error else false
 * - @link doValidation() : performs the validation of form 
 * - @link isBlank($value) : validation for blank
 * - @link isEmail($value, $option) : validation for email 
 * - @link isDate($value, $option) : validation for date 
 * - @link isNumeric($value, $option) : validation for numeric
 * - @link getValidationType($FieldName) : get the validation flag
 * - @link getFieldName($FieldName) : get field type flag
 * - @link ccCheck($CCNumber) :  valdiation for credit card number
 *  
 * @author pawan
 */

class FormValidator {
	var $error_msg;
	var $PARAMS;
	var $error_box;

	/**
	 * Constructor of the class
	 * 
	 * This initialize the variable which is used to other methods of class
	 * 
	 * @param Array $_POST requires all the posted data of a form
	 */
	
	function FormValidator($post_vars)	{
		
		$this->PARAMS=$post_vars;
		$this->error_msg="";
	}
	
	/**
	 * Returns the html code of 
	 * 
	 * Adds error to a html table and returns that
	 * 
	 * @return String html table of error having each error in a new line 
	 */
	
	function getErrorBox() {
			$this->error_box='<center><table border="1"><tr bgcolor="#FF0000">' .
					'<th><font color="#FFFFFF">Error(s)</font></th></tr><tr><td>' .
					'<font color="#FF0000">'.$this->error_msg.'</font></td></tr>' .
							'</table></center>';
			return $this->error_box;
	}

	/**
	 * Return only error message
	 * 
	 * @return String Error message string, every single error in a new line
	 */

	function getErrorMessage() {
			return $this->error_msg;
	}	
	
	/**
	 * setting an error explicitly
	 * 
	 * sets an error message for the validation, which this class can not 
	 * perform
	 * 
	 * @param String $msg Message text.
	 * @return Boolean true 
	 * 
	 */
	function setErrorMessage($msg) {
				$this->error_msg .= "<li>".$msg."</li>";
				return true;
	}
	
	/**
	 * return the true in case error else false
	 * 
	 * @return Boolean true in case of error else false. 
	 */
	function isError() {
		if(trim(strlen($this->error_msg))>0) return true;
		else return false;
	}
	
	/**
	 * doValidation is used to perform the validation 
	 * 
	 * this method uses all the posted value and check its flag and perform 
	 * the validation.
	 * 
	 * @param none
	 * @return none
	 */
	function doValidation() {
		foreach ($this->PARAMS as $key => $value)
		{
			$validationFlag = $this->getValidationType($key);
			$fieldName = $this->getFieldName($key);
			switch($validationFlag)
			{
				case "0" : // No validation required
						break;
				case "1" : // Validation for "Not Blank"
						if($this->isBlank($value,"")) $this->error_msg .="<li>".$fieldName." can not be blank</li>";
						break;
				case "2" : // Validation for "Not Blank email"
						if(!$this->isEmail($value,"b")) $this->error_msg .="<li>Invalid or No ".$fieldName."</li>";
						break;
				case "3" : // Validation for "Option email"
						if(!$this->isEmail($value,"")) $this->error_msg .="<li>Invalid ".$fieldName."</li>";
						break;
				case "4" : // Vaidation for "Not Blank Date"
						if(!$this->isDate($value,"b")) $this->error_msg .="<li>Invalid or No ".$fieldName."</li>";
						break;
				case "5" : // Validation for "Optional Date"
						if(!$this->isDate($value,"")) $this->error_msg .="<li>Invalid ".$fieldName."</li>";
						break;
				case "6" : // Validation for "Not Blank Numeric"
						if(!$this->isNumeric($value,"b")) $this->error_msg .="<li>Invalid or No ".$fieldName."</li>";
						break;
				case "7" : // Validation for "Optional Numeric "
						if(!$this->isNumeric($value,"")) $this->error_msg .="<li>Invalid ".$fieldName."</li>";
						break;
			}
		}
	}

////////////////////////////////////////////////
// Function for Blank Check										//
////////////////////////////////////////////////

/**
 * check for a blank entry
 * 
 * @param String $value value to be checked for blank
 * @return Boolean true in case of blank else false
 */

	function isBlank($value) {
		if(trim($value)=="") return true;
		else return false;
	}
	
////////////////////////////////////////////////////
// Function for Email Validation									//
////////////////////////////////////////////////////
/**
 * validation for email 
 * 
 * @param String $value value of the form field
 * @param String $option b in case of blank entry validation is also need 
 * else null
 * @return Boolean true in case of valid email else false  
 */
	function isEmail($value, $option) {
		if($option=="b")
		{
			if($this->isBlank($value)) return false;
			elseif(strlen($value)<5) return false;
			elseif(!is_integer(strpos($value,"@")) || !is_integer(strpos($value,".")))  return false;
		}
		elseif(!$this->isBlank($value))
		{
			if(strlen($value)<5) return false;
			elseif(!is_integer(strpos($value,"@")) && !is_integer(strpos($value,"."))) // in versions older than 4.0b3:
			return false;
		}
		return true;
	}
	
////////////////////////////////////////////////////
// Function for Date Validation										//
////////////////////////////////////////////////////

/**
 * validation for date 
 * 
 * Note the date must be in format of mm-dd-yyyy
 * 
 * @param String $value value of the form field
 * @param String $option b in case of blank entry validation is also need 
 * else null
 * @return Boolean true in case of valid date else false  
 */

	function isDate($value, $option) {
		if($option=="b" && $this->isblank($value)) return false;
		//we are assuming the date format as mm-dd-yyyy
		if(!is_integer(strpos($value,"-"))) return false;
		$date_array=explode("-",$value);
		$val=checkdate($date_array[0],$date_array[1],$date_array[2]);
		if($val==true) return true;
		else return false;
	}
	
//////////////////////////////////////////////////////
// Function for Numeric Validation									//
//////////////////////////////////////////////////////

/**
 * validation for Numeric 
 * 
 * @param String $value value of the form field
 * @param String $option b in case of blank entry validation is also need 
 * else null
 * @return Boolean true in case of valid numeric value else false  
 */

	function isNumeric($value, $option) {
		
		if($option=="b")
		{
			if($this->isblank($value)) return false;
			if(is_numeric($value)) return true; 
			else return false; 
		}
		elseif(!$this->isBlank($value))
		{
			if(is_numeric($value)) return true;
			else return false; 
		}	
		else return true;
	}
	
//////////////////////////////////////////////////////
// Function to get Validation type									//
//////////////////////////////////////////////////////

/**
 * returns valdiation flag of form field
 * 
 * @return String last charector of fieldname
 */
	function getValidationType($name) {
		return(substr($name,-1));
	}

//////////////////////////////////////////////////////
// Function to get Form Field Name									//
//////////////////////////////////////////////////////
/**
 * returns Field name to display in error
 * 
 * @return String label taken from the name
 */
	function getFieldName($name) {
		return(ucwords(strtolower(str_replace("_"," ",substr($name,(strpos($name,"_")+1),(strrpos($name,"_")-2))))));		
	}	

//////////////////////////////////////////////////////
// Function to Validate Credit Card Number					//
//////////////////////////////////////////////////////

/**
 *  Validation for credit cards
 * 
 * @param String $str credit card number
 * @return Boolean true in case of correct else false
 */

	function ccCheck($str) {
	 $result = true;
	 $sum = 0; 
	 $mul = 1; 
	 $strLen1 = strlen($str);

	 for ($i = 0; $i < $strLen1; $i++) 
	 {
		 $digit = substr($str,-1);
		 $str = substr($str,0,strlen($str)-1);
		 $tproduct = $digit*$mul;
		 if ($tproduct >= 10)
			 $sum += ($tproduct % 10) + 1;
		 else
			 $sum += $tproduct;
		 if ($mul == 1)
			 $mul++;
		 else
			 $mul--;
	 }
	 if (($sum % 10) != 0)
		 $result = false;
	 return $result;
	}	
}	
?>