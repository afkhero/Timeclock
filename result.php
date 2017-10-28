<?php

# Result class, used to encapsulate results in a managable interface
# 
# To return a success use 'return Result::success()'
# This will create a new Result object with a successful 'error' code
# 
# After calling a function that returns a result you can then evaluate it
#  by using '$result->evalute($desc)'
#
# evalute($desc) sets $desc to a a string describing the result and returns
#  a true/false value based upon whether it succeeded or not
#
# Do not create a Result using new, use one of the static functions below:
#  Result::success()      returns result with code set to SUCC that evaluates true
#  Result::e_connection() returns result with code BAD_DB, evaluates false
#  Result::e_emptySet()   returns result with code EMPTY_RES, evaluates false
#  Result::e_ambiguous()  returns result with code MULTI_RES, evaluates false
#  Result::e_edit()       returns result with code BAD_EDIT, evaluates false

class Result {
	const BAD_DB = 1;	#connection to DataBase is Null or otherwise broken
	const EMPTY_RES = 2;#the query we just executed was expected to return something but returned nothing
	const MULTI_RES = 3;#the query was expected to return 1 or nothing but returned alot of things
	const BAD_EDIT = 4;	#the query to edit(INSERT, UPDATE, DELETE) failed
	const SUCC = 0;		#we succeeded!!!

	private $error;

	public function __construct($code){
		$this->error = $code;
	}

	public static function success(){
		return new Result(Result::SUCC);
	}

	public static function e_connection(){
		return new Result(Result::BAD_DB);
	}

	public static function e_emptySet(){
		return new Result(Result::EMPTY_RES);
	}

	public static function e_ambiguous(){
		return new Result(Result::MULTI_RES);
	}

	public static function e_edit(){
		return new Result(Result::BAD_EDIT);
	}

	public function evaluate(&$desc){
		if(isset($this->error)){
			switch($this->error){
				case Result::SUCC: $desc = 'Execution successful'; return true;
				case Result::BAD_DB: $desc = 'Database connection is invalid'; return false;
				case Result::EMPTY_RES: $desc = 'Expected one or more tuples and recieved none'; return false;
				case Result::MULTI_RES: $desc = 'Expected one or no tuples and recieved many'; return false;
				case Result::BAD_EDIT: $desc = 'Database edit could not be carried out'; return false;
			}
		}
		$desc = 'Invalid use of Result';
		return false;
	}
}

?>