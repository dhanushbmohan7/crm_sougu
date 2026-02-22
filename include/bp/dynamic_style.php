 <style>
 	
	<?php 
	$style_query="select * from styles where reset_flag='0';";
	$style_sql=$obj->generalquery($style_query);
	while($style_arr=mysqli_fetch_array($style_sql))
	{
		
	echo $style_arr['style_class']." {";	
		
		echo "background:#".$style_arr['back_color']." !important; ";
		
		echo "color:#".$style_arr['font_color']." !important; ";
		
	echo  "}";	
	}
	?>
	
  </style>