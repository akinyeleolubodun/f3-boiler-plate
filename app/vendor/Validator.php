<?php

/**
*   -------------------------------------------------------------------------
*             PHP Form Validator (formvalidator.php)
*             Version 1.0
*   -------------------------------------------------------------------------
*   Author: Md. Monjur-ul-Hasan. (M H Rasel)
*   Email: mail@monjur-ul-hasan.info
*   Release: 2 Nov 2009
*   Copyright 2009
*   Documentation: http://phpformvalidation.monjur-ul-hasan.info
*   
*   @requires    PHP 4 >= 4.3.2 
* 
*   License: under  GNU GENERAL PUBLIC LICENSE, Version 1.0, August 2009
* 
**/


class Validator
{
	var $_errmessages;
	var $_rules;
	var $_errors;
	var $_messages;
	var $_defauleErrorMessage;
	var $_defaultDateFormat;
	var $_data;
	var $_curElement;
	
	function Reset()
	{
		$this->_errormessages	= array();
		$this->_rules 			= array();
		$this->_messages		= array();
		$this->_defauleErrorMessage = array();
	}

	function setDefaultDateFormat($format)
	{
	    $this->_defaultDateFormat = $format;
	}
    
	function Validator($rules="",$messages="")
	{
		$this->Reset();
		$this->setDefaultDateFormat("mmddyy");
		if(is_array($rules))	$this->_rules = $rules;
		if(is_array($messages))	$this->_errmessages = $messages;

	}
	
	function addRules($rules,$reset=false)
	{
		if($reset)
		
		$this->Reset();
		$this->_rules = array_merge($this->_rules,$rules);
	}
	
	function addErrors($errors,$reset=false)
	{
		if($reset)
		$this->Reset();
		$this->_errmessages = array_merge($this->_errmessages,$errors);
	}
	
	function isValid($data)
	{
		$valid = true;
		$this->_data = $data;
		foreach($data as $element => $value)
		{
			if(isset($this->_rules[$element]))
			{
				if(!$this->validate($element,$value))
					$valid = false;
			}
		}
		return $valid;
	}
	
	function validate($element, $value)
	{
		$this->_curElement = $element;
		$rules = $this->_rules[$element];

		if(isset($this->_errmessages[$element]))
			$errormessage = $this->_errmessages[$element];

		if(is_array($rules))
		{
			
			$valid = true;
			$curErr = array();
			foreach($rules as $rule=>$con)
			{
				
				if(!$this->check($rule,$value,$con))
				{
					$valid = false;
					if(isset($errormessage[$rule]))
						$curErr[]=$errormessage[$rule];
					else $curErr[]=$this->DefaultErrorMsg($rule);
				}
			}
            
		if(!$valid && !isset($this->_messages[$element]))
			$this->_messages[$element] = $curErr;
			return $valid;
		}
		else
		{
			if(!$this->check($rules,$value))
			{
				if(isset($errormessage))
					$this->_messages[$element] = $errormessage;    
				else $this->_messages[$element] = $this->DefaultErrorMsg($rules);
		
				return false;
			}
			else return true;
			
		}
	}
	
	function check($rule,$value,$condition=true)
	{
		
		switch(strtolower($rule))
		{
			case "require"		:	return !(trim($value)=="");		//	
			case "maxlength"	:	return (strlen($value)<=$condition);//		
			case "minlength"	:	return (strlen($value)>=$condition);	//	
			case "eqlength"		:	return (strlen($value)==$condition);//		
			case "equal"		:	return ($value==$condition);//
			case "numeric"		:	return is_numeric($value);//
			case "int"			:	$v = (int) $value;
									return ((string)$v===(string)$value);
			
			case "float"		:	$v = (float) $value; 
									return ((string)$v===(string)$value);
									
			case "min"			:	if($value<$condition)				return false; 	break;
			case "max"			:	if($value>$condition)				return false; 	break;
			case "gt"			:	if($value<$condition)				return false; 	break;
			case "lt"			:	if($value>$condition)				return false; 	break;
			
			
			default:	
				if(method_exists($this,$rule))
				{
					return @call_user_method($rule,$this,$condition,$value);
				}
				else die("Error: rule '".$rule."' not found");
				
		}		
		return true;
	}
	
	function DefaultErrorMsg($rule)
	{
        
		switch(strtolower($rule))
		{
		    case "require"      :    return "Require";        //    
		    case "maxlength"    :    return "Over Length"; //        
		    case "minlength"    :    return "Under Length";    //    
		    case "eqlength"     :    return "Length Mismatch";//        
		    case "equal"        :    return "Data Mismatch";//
		    case "numeric"      :    return "Numeric Value Require";//
		    case "int"          :    return "Integer Value require";
		    case "float"        :    return "Float Value require";
		    case "gt"           :
		    case "min"          :    return "Too small";
		    case "lt"           :
		    case "max"          :    return "Too high";
		    case "date"         :    return "Invalid Date";
	
		    default             :    return "error";
			
		}        
		
		return true;
	}
	
	function CountError()
	{
	    return count($this->_messages);
	}
	
	function ErrorFields()
	{
    //      print_r($this->_errmessages);
	    return array_keys($this->_messages);
	}
	
	function getErrors($element)
	{
	    return $this->_messages[$element];
	}
    
	function date($con,$value)
	{
		
		$d = $value;
		if(!preg_match("/^((\d){1,2})[\/\.-]((\d){1,2})[\/\.-](\d{2,4})$/",$d,$matches))
			return false;//Bad Date Format
		$T = split("[\/\.-]",$d);
		if(is_array($con))    
		{
		    if(isset($con["format"]))
			$format = $con["format"];
		    else $format = $this->_defaultDateFormat;
		}
		else $format = $this->_defaultDateFormat;
        
		switch($format)
		{
		    case "mmddyyyy":
			$M = (int)$T[0];
			$D = (int)$T[1];
			$Y = (int)$T[2];
			if($Y<999)
			    return false;
	
		    break;
		    case "mmddyy":
			//print_r($T);
			$M = (int)$T[0];
			$D = (int)$T[1];
			$Y = (int)$T[2];
			if($Y>99)
			    return false;
		    break;
		    case "ddmmyyyy":
			$D = (int)$T[0];
			$M = (int)$T[1];
			$Y = (int)$T[2];
			if($Y<999)
			    return false;
		    break;
		    case "ddmmyy":
			$D = (int)$T[0];
			$M = (int)$T[1];
			$Y = (int)$T[2];
			if($Y>99)
			    return false;
		    break;
		}
            
		$MON=array(0,31,28,31,30,31,30,31,31,30,31,30,31);
		return $D>0 && ($D<=$MON[$M] ||	$D==29 && $Y%4==0 && ($Y%100!=0 || $Y%400==0)); 
	}
	
	function depend($con,$value)    
	{
    //        die($con);
	    if($this->check("require",$this->_data[$con['depend_on']]))
	    {
		
		$valid = true;
		$curErr = array();
		foreach($con as $rule=>$con)
		{
		    if($rule!='depend_on')                
			if(!$this->check($rule,$value,$con))
			{
			    $valid = false;
			    if(isset($errormessage[$rule]))
				$curErr[]=$errormessage[$rule];
			    else $curErr[]=$this->DefaultErrorMsg($rule);
			}
		}
		if(!$valid)
		    $this->_messages[$this->_curElement] = $curErr;
		return $valid;
	    }
	    
	    else return true;
	}
	
}
  

?>