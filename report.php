<?php

// Report: A class that aims to aid in coversion of data.
// Report takes some strings(title and footer) that represent superficial info at the top and bottom of the document.
// It also takes a 2d array of strings representing the body of the document.
// It can then represent that data in multiple string formats:
//     1) json encoded
//     2) html table encoded
//     3) csv encded

class Report{
	private $title;  //a string with the title in it
	private $body;	 //an array of tuples that make up the body 
	private $footer; //a string with footer stuff in it

	public function __construct($titlestr, $bodyobj, $footerstr){
		if(isset($titlestr)){
			$this->title = $titlestr;
		}
		if(isset($bodyobj)){
			$this->body = $bodyobj;
		}
		if(isset($footerstr)){
			$this->footer = $footerstr;
		}
	}

	public function json(){
		$obj = array();
		if(isset($this->title)){
			$obj['title'] = $this->title;
		}
		if(isset($this->body)){
			$obj['body'] = $this->body;
		}
		if(isset($this->footer)){
			$obj['footer'] = $this->footer;
		}
		return json_encode($obj);
	}

	public function html(){
		$html = "<p>";
		if(isset($this->title)){
			$html = $html.'<h1>';
			$html = $html.$this->title.'</h1><br>';
		}
		if(isset($this->body)){
			$html = $html.'<table>';
			$lines = count($this->body);
			if($lines > 0){
				$html = $html.'<tr>';
				foreach ($this->body[0] as $item) {
					$html = $html.'<th>'.$item.'</th>';
				}
				$html = $html.'</tr>';

				for($i = 1; $i < $lines; $i = $i + 1){
					$html = $html.'<tr>';
					foreach($this->body[$i] as $item){
						$html = $html.'<td>'.$item.'</td>';
					}
					$html = $html.'</tr>';
				}
			}	
			$html = $html.'</table>';
		}
		if(isset($this->footer)){
			$html = $html.$this->footer.'<br>';
		}
		return $html.'</p>';
	}

	public function csv(){
		$csv = "";
		if(isset($this->title)){
			$csv = $csv.$this->title.',\n';
		}
		if(isset($this->body)){
			foreach($this->body as $line){
				foreach($line as $item){
					$csv = $csv.$item.', ';
				}
				$csv = $csv.'\n';
			}
		}
		if(isset($this->footer)){
			$csv = $csv.$this->footer.'\n';
		}
		return $csv;
	}

	public function setTitle($str){
		$this->title = $str;
	}

	public function setBody($array){
		$this->body = $array;
	}

	public function setFooter($str){
		$this->footer = $str;
	}
}

?>