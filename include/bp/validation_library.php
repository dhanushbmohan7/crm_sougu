<?php
class Validation
{
	var $con;
	var $ret;
	
	
	
	
	
function map_array($value,$mul,$response)
{

$map=array(
"1"=>"1",
"2"=>"2",
"3"=>"3",
"4"=>"4",
"5"=>"5",
"6"=>"6",
"7"=>"7",
"8"=>"8",
"9"=>"9",
"10"=>"A",
"11"=>"B",
"12"=>"C",
"13"=>"D",
"14"=>"E",
"15"=>"F",
"16"=>"G",
"17"=>"H",
"18"=>"I",
"19"=>"J",
"20"=>"K",
"21"=>"L",
"22"=>"M",
"23"=>"N",
"24"=>"O",
"25"=>"P",
"26"=>"Q",
"27"=>"R",
"28"=>"S",
"29"=>"T",
"30"=>"U",
"31"=>"V",
"32"=>"W",
"33"=>"X",
"34"=>"Y",
"35"=>"Z");		
	
	if($response==0)
	{
		
$pos=array_search($value,$map);

$first=($pos*$mul);
$second =(int)($first/36);
$third = ($first%36);

$fourth=$second+$third;

//return "value".$value."pos".$pos."//first".$first."//second".$second."//third".$third."//fourth".$fourth;

return $fourth;

	}
	else
	{
	
	
	if($value==36 || $value==0)
		{
			return 0;
		}
		else
		{
			return $map[$value];
		}
	}
}






function get_check_code($string)
{

$string=strtoupper($string);


$string_array=str_split($string);

/*print_r($string_array);

echo "<br>";*/

$total=0;

foreach ($string_array as $key=>$value) {
	$key=$key+1;
	$place_value=$key%2;
	
	if($place_value==1)
	{
	//odd
	$mul=1;	
	}
	else
	{
	$mul=2;		
	}
//echo map_array($value,$mul,0)."<br>";	
$total += $this->map_array($value,$mul,0);
}

//echo "sum=".$total."<br>";

$mod=$total%36;

$p=36-$mod;

//echo "P value = ".$p."<br>";

//reverse Map (get check code here)

return $this->map_array($p,1,1);

}



	
	
	
function validate_gstin_no($gst)
{

	$pattern="/^([0-9]){2}([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}([0-9a-zA-Z]){1}([a-zA-Z]){1}([0-9a-zA-Z]){1}?$/";
$val=preg_match($pattern,$gst);	
// true return 1 else 0
	if($val==1)
		{
		$last=substr($gst, -1);	
		$string=substr($gst,0,14);
		$check_code=$this->get_check_code($string);
		
			if($last==$check_code)
			{
			$true=1;	
			}
			else
			{
			$true=0;	
			}
			
		}
		else
		{
		$true=$val;
		}
	
	return $true;		
}

function isDate($string) 
{

    $pattern = '/^([0-9]{2})\\-([0-9]{2})\\-([0-9]{2,4})$/';
   $val=preg_match($pattern, $string);
    return $val;
}	



function isNumber($string) 
{

    $pattern = '/^[0-9]+(\.[0-9]{1,3})?$/';
   $val=preg_match($pattern, $string);
    return $val;
}


function numberToWords($number)
{

//$number = 190908100.25;
   $no = round($number);
   $point = round($number - $no, 2) * 100;
   
   if($point<0)
   {
	$point=($point*-1);   
   }
   
   $decimal1=$point / 10;
   $decimal2=$point % 10;;
   
   $hundred = null;
   $digits_1 = strlen($no);
   $i = 0;
   $str = array();
   $words = array('0' => '', '1' => 'one', '2' => 'two',
    '3' => 'three', '4' => 'four', '5' => 'five', '6' => 'six',
    '7' => 'seven', '8' => 'eight', '9' => 'nine',
    '10' => 'ten', '11' => 'eleven', '12' => 'twelve',
    '13' => 'thirteen', '14' => 'fourteen',
    '15' => 'fifteen', '16' => 'sixteen', '17' => 'seventeen',
    '18' => 'eighteen', '19' =>'nineteen', '20' => 'twenty',
    '30' => 'thirty', '40' => 'forty', '50' => 'fifty',
    '60' => 'sixty', '70' => 'seventy',
    '80' => 'eighty', '90' => 'ninety');
   $digits = array('', 'hundred', 'thousand', 'lakh', 'crore');
   while ($i < $digits_1) {
     $divider = ($i == 2) ? 10 : 100;
     $number = floor($no % $divider);
     $no = floor($no / $divider);
     $i += ($divider == 10) ? 1 : 2;
     if ($number) {
        $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
        $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
        $str [] = ($number < 21) ? $words[$number] .
            " " . $digits[$counter] . $plural . " " . $hundred
            :
            $words[floor($number / 10) * 10]
            . " " . $words[$number % 10] . " "
            . $digits[$counter] . $plural . " " . $hundred;
     } else $str[] = null;
  }
  $str = array_reverse($str);
  $result = implode('', $str);
  $points = ($point) ?
    " And " . $words[$decimal1] . " " . 
          $words[$decimal2] : '';
	
	
	if($points!="")	  
		 {
  return $result . "Rupees ".$points . " Paise Only";	
		 }
		 else
		 {
   return $result . "Rupees Only";			 
		 }
		 
	
}


	
}