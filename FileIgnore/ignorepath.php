<?php
/**
 * PHP Path Parser
 * @package phppathparser
 */
class IgnorePath {

	public $debug = false;
	private $_base = '';
	private $_ruleTree = array();
	private $_ruleExcludeTree = array();
	private $_regExList = array();
	private $_regExExcludeList = array();

	/**
	 * __construct
	 * @param $name
	 * @return mixed
	 */
	public function __construct($basePath = '', $rulePath = '') {
		$this->_base = $basePath;
		if ($rulePath != '') {
			$this->loadRules(file_get_contents($rulePath));
		}
	}


	/**
	 * buildTree
	 * @param $deep
	 * @param $array
	 * @param $tree
	 * @return bool
	 */
	private function buildTree($deep, $array, &$tree) {
		if ($this->debug) { echo 'deep = ' . $deep . ', $array[$deep] = ' . $array[$deep] . "\n";}
		if ($deep == count($array)) return;
		$val = $array[$deep];
		//if ($val == '') { $val = '$base'; }
		$childTree = &$tree;
		if (!isset($tree[$val])) {
			$tree[$val] = array();
		}
		$childTree = &$tree[$val];
		$this->buildTree($deep + 1, $array, $childTree);
		return true;
	}


	/**
	 * buildRegExpList
	 * @param $string
	 * @param $list
	 * @return bool
	 */
	private function buildRegExpList($string, &$list) {

		$string = substr($string, ($string[0] == '!' ? 2 : 1));
		$pattern = substr($string, 0, strpos($string, "|"));
		$main = substr($string, strpos($string, "|") + 1);
		$regex = "/" . $main . "/" . $pattern;
		array_push($list, $regex);
		return true;

	}


	/*
	 * analyze
	 * @param $deep
	 * @param $array
	 * @return bool
	 */
	private function _analyze($deep, $array, $tree) {
		//echo $deep;
		
		$val = $array[$deep];
		//if ($val == '') { $val = '$base'; }
		//echo $treeName;

		foreach ($tree as $key => $child) {
		 	$str = preg_quote($key);
			$str = str_replace('\\*', '.+', $str);
			$str = str_replace('\\?', '.', $str);
			$str = "/" . $str . "/";

			//var_dump($str);var_dump($val);echo "\n". (preg_match($str, $val) ? "a" : "b");
			if ($this->debug) {
				echo "deep = " . $deep . ", RegEx = " . $str . ", Value = ". $val . ", Result = " . (preg_match($str, $val) ? "True" : "False") . "\n";
			}
			if (preg_match($str, $val)) {
				if ($deep == count($array) - 1 || count($child) == 0) {
					return true;
				}
				if ($this->_analyze($deep + 1, $array, $child)) {
					return true;
				} else {
					continue;
				}
			}
			else {
				continue;
			}
		}

		return false;

	}

	/**
	 * checkRegExp
	 * @param $path
	 * @return bool
	 */
	private function _checkRegExp($path, $array) {
		foreach ($array as $key => $value) {
			if ($this->debug) { echo "Check RegExp: " . $value . " , Result: " . (preg_match($value, $path) ? "True" : "False") . "\n";}  
			if (preg_match($value, $path)) return true;
		}
		return false;
	}


	/**
	 * loadRules
	 * @param $ruleText
	 * @return bool
	 */
	public function loadRules($ruleText) {
		$array = explode("\n", $ruleText);
		if ($this->debug) { var_dump($array); }
		for ($i = 0; $i < count($array); $i++) {
			//echo $array[$i][0] . "\n";
			switch ($array[$i][0]) {
				case "#":
					// Ignore
					break;
				case ":":
					// RegExp
					$this->buildRegExpList($array[$i], $this->_regExList);
					break;
				case '!':
					if ($array[$i][1] == ':') {
						$this->buildRegExpList($array[$i], $this->_regExExcludeList);
					} else {
						$this->buildTree(0, explode("/", substr($array[$i], 1)), $this->_ruleExcludeTree);
					}
					break;
				default:
					$this->buildTree(0, explode("/", $array[$i]), $this->_ruleTree);
					
					break;
			}
		}
		if ($this->debug) {var_dump($this->_ruleTree);}
		
	}


	/**
	 * checkPath
	 * @param $path
	 * @return bool
	 */
	public function checkPath($path) {

		// False means exclude
		// True means include

		$array = explode("/", $path);

		// Check exclude by RegExp first
		if ($this->debug) { echo "Check Exclude RegExp: \n";}
		if ($this->_checkRegExp($path, $this->_regExExcludeList)) return false;
		// Check include by RegExp second
		if ($this->debug) { echo "Check Include RegExp: \n";}
		if ($this->_checkRegExp($path, $this->_regExList)) return true;
		if ($this->debug) { echo "Check Include: \n";}
		if ($this->_analyze(0, $array, $this->_ruleTree)) {
			// If found then check exclude
			if ($this->debug) { echo "Check Exclude: \n";}
			return !$this->_analyze(0, $array, $this->_ruleExcludeTree);
		} else {
			return false;
		}

		
	}


}