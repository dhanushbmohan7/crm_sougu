<?php //echo "SP NAME :".$viewquery."<br>"; 
        	
			$t_storedprocedure="CALL ".$viewquery."('$uid');";
			//echo $t_storedprocedure."<br>";
		
		
		
	if(isset($_POST['tablesettings']))
{
	$h=$_POST['rowid'];
	
	//echo "row id=".$h."<br>";
	$length='';
	$colname='';
	$format='';
	$ali='';
	
			foreach($_POST['wid'] as $selected)
			{
			$length.=$selected."`";
			}
			foreach($_POST['colname'] as $selected)
			{
			$colname.=$selected."`";
			}
			
			foreach($_POST['format'] as $selected)
			{
			$format.=$selected."`";
			}
			
			foreach($_POST['ali'] as $selected)
			{
			$ali.=$selected."`";
			}
			
			
			$query2="update tab101 set fld10109='$length', fld10108='$colname', FLD10112='$format', FLD10114='$ali' where fld10101='$h';";
			$sql2=$obj2->generalquery($query2);
			if($sql2)
			{
			?>
            <script>
	alert("Table Settings Changed Successfully");
	window.location.href='<?php echo $filename; ?>.php?sort=<?php echo $sort; ?>';
			</script>
            <?php	
			}
			else
			{
				?>
                 <script>
		alert("Table Settings Change Failed");
			</script>
                <?php
			}
			
			
}	
		
	
		
		if($fields!='')
	{
	$fields=substr($fields, 0, -1);
	$pieces = explode("`", $fields);
	$fieldcount=sizeof($pieces);
	}	
	
	
	$widthfields=substr($widthfields, 0, -1);
	$widthpieces = explode("`", $widthfields);
	
	@$formatfields=substr($formatfields, 0, -1);
	$formatpieces = explode("`", $formatfields);
	
	
	@$alighnfields=substr($alighnfields, 0, -1);
	$alighnpieces = explode("`", $alighnfields);
				
				
				
				$t_res = $obj2->generalquery($t_storedprocedure);
	$t_count=mysqli_num_rows($t_res);
	$queryfields=mysqli_num_fields($t_res);
	

	if($t_count>0)
	{
		
	$t_row = mysqli_fetch_assoc($t_res);
	
	//$t_arr5=mysqli_fetch_field($t_res);
	
	$t_html="<a tabindex='-1'  class='dt-button buttons-copy buttons-html5' id='print_btn_chk' tabindex='0' onclick='custom_call_print()' ><span>Print</span></a>";
	
	$t_html.="<a tabindex='-1' class='dt-button buttons-copy buttons-html5' id='print_btn_dos_chk' tabindex='0' onclick='custom_call_dos_print()' ><span>Print Dos</span></a><div id='only_table'>";
	
	$t_html.="<table border='1' class='table table-bordered table-striped responsive-utilities jambo_table white' id='dynamic_ajax_table'>";
	
	if($fields!='' && $fieldcount==($queryfields-1))
	{

	$t_html.='<colgroup>';
	$t_html.='<col width="50" />';
		foreach($widthpieces as $key7=> $width) 
		{
		if($widthpieces[$key7]>0)
				{	
	$t_html.='<col width="'.$width.'" />';
	
				}
		}
		
		if(@$view_edit==1)
		{
		$t_html.='<col width="70" />';
		}
		$t_html.='<col width="70" />';
		if(@$reciept_print==1)
				{
				$t_html.='<col width="60" />';	
				}
				
				if(@$profile_pic==1)
				{
				$t_html.='<col width="90" />';	
				}

	$t_html.='</colgroup>';
	}
	$dynamic=array();
	foreach(array_slice($t_row,1) as $t_name => $t_value) {	
		
				array_push($dynamic,$t_name);
				
				}
	
	$t_html.='<thead>';
	
	if($fields!='' && $fieldcount==($queryfields-1))
	{
	$t_html.='<tr  class="headings">';
	$t_html.="<th align='right'>Sl</th>";
	
	foreach($pieces as $key6=> $name) {	
	if($widthpieces[$key6]>0)
			{		
				$t_html.="<th align='right'>$name</th>";
				
			}
		
				}
		if(@$view_edit==1)
		{
		$t_html.="<th width='70' align='right'></th>";
		}
		
		$t_html.="<th width='70' align='right'></th>";
		if(@$reciept_print==1)
				{
				$t_html.="<th width='60'></th>";	
				}
				
				if(@$profile_pic==1)
				{
				$t_html.="<th width='90'></th>";	
				}
		
	$t_html.='</tr>';
	}
	else
	{
	
	unset($widthpieces);
	$widthpieces=array();
	unset($pieces);
	
	
	unset($alighnpieces);
	$alighnpieces=array();
	unset($formatpieces);
	$formatpieces=array();
	
	$t_html.='<tr  class="headings">';
	$t_html.="<th align='right'>Sl</th>";
		foreach(array_slice($t_row,1) as $t_name => $t_value) {	
		
				$t_html.="<th align='right'>$t_name</th>";
				array_push($widthpieces,"100");
				array_push($alighnpieces,"16");
				array_push($formatpieces,"S");
				}
				
				if(@$view_edit==1)
				{
				$t_html.="<th width='70' align='right'></th>";
				}
				
				$t_html.="<th width='70' align='right'></th>";
				if(@$reciept_print==1)
				{
				$t_html.="<th width='60' align='right'></th>";	
				}
				
				if(@$profile_pic==1)
				{
				$t_html.="<th width='90' align='right'></th>";	
				}
				
	
	
			$t_html.='</tr>';
	}
			$t_html.='</thead>';
			
			$t_i=1;
			
			$t_html.='<tfoot style="display: table-header-group;"><tr >';
	$t_html.="<th align='right'>Sl</th>";
		foreach(array_slice($t_row,1) as $t_name => $t_value) {	
		
				$t_html.="<th align='right'>$t_name</th>";
				
				
				}
				
				if(@$view_edit==1)
				{
				$t_html.="<th width='70' align='right'>Edit</th>";
				}
				
				$t_html.="<th width='70' align='right'>Remove</th>";
				
				if(@$reciept_print==1)
				{
				$t_html.="<th width='60' align='right'>Print</th>";	
				}
				
				if(@$profile_pic==1)
				{
				$t_html.="<th width='90' align='right'>ProfilePic</th>";	
				}
	
			$t_html.='</tr></tfoot><tbody>';
			
			
				while($t_row) 
				{
					if($t_i%2==0)
					{
					$t_rowclass="even pointer";	
					}
					else
					{
					$t_rowclass="odd pointer";		
					}
					
					$t_row = array_values($t_row);
					$t_html.='<tr  class="'.$t_rowclass.'">';
					$t_html.="<td>$t_i</td>";
						foreach($t_row as  $t_x =>  $t_value)
						 {
							 if($t_x==0)
							 {
								$t_hiddenid=$t_value; 
							 }
							 
							 if($t_x>0)
							 {
								if(@$widthpieces[$t_x-1]>0)
									{
								if($alighnpieces[$t_x-1]=='16')
										{
										$alighn="left";	
										}
										
										if($alighnpieces[$t_x-1]=='32')
										{
										$alighn="center";	
										}
										
										if($alighnpieces[$t_x-1]=='64')
										{
										$alighn="right";	
										}
										
										
								$t_html.="<td  align='$alighn' >".$obj->convert_format($formatpieces[$t_x-1],$t_value)."</td>";
									
									
									
									}
								 
							 }
							
							 
						}
						if(@$edit_button=="C")
						{
						$button_cap1="Change";	
						}
						else
						{
						$button_cap1="Edit";		
						}
						
						
					/*$t_html.="<td><form method='post'>
                    <input type='hidden' value='$t_hiddenid' name='edit' >
                    <input type='submit' name='btnedit' value='".$button_cap1."' class='btn btn-success btn-sm' />
                    </form></td>";
					*/
					
					if(@$view_edit==1)
					{
					$t_html.='<td class="masked"><button type="button" data-id="'.$t_hiddenid.'" class="edit_modal_call btn btn-success btn-sm" data-toggle="modal" data-target="#edit_Modal" >Edit</button></td>';
					}
					
						
					
					/*$t_html.="<td><form method='post' onSubmit='return confirm_remove()'>
                    <input type='hidden' value='$t_hiddenid' name='remove'>
                    <input type='submit' name='btnremove' value='Remove'  class='btn btn-danger btn-sm' />
                    </form></td>"; */
					
					
					$t_html.='<td class="masked"><button type="button" data-id="'.$t_hiddenid.'" class="remove_modal_call btn btn-danger btn-sm" data-toggle="modal" data-target="#remove_Modal">Remove</button></td>';
					
					
					
					
					if(@$reciept_print==1)
				{
				$t_html.="<td class='masked'>
				
				
				<form >
                    
                    </form>
				
				<form method='post'  target='_blank' action='".$reciept_filename.".php' >
                    <input type='hidden' value='$t_hiddenid' name='print_id'>
                    <input type='submit' tabindex='-1'  value='Print'  class='btn btn-info btn-sm'/>
                    </form>
					
					</td>";	
				}	
				
				if(@$profile_pic==1)
				{
				$t_html.="<td class='masked'>
				
					<form method='post'  target='_blank' action='".$profile_pic_filename.".php' >
                    <input type='hidden' value='$t_hiddenid' name='print_id'>
                    <input type='submit' tabindex='-1'  value='Img Upload'  class='btn btn-warning btn-sm'/>
                    </form>
					
					</td>";	
				}	
						
						
				$t_html.='</tr>';
				$t_row = mysqli_fetch_assoc($t_res);
				$t_i++;
			
				}
				$t_html.='</tbody></table></div>';
				
				echo $t_html;
	}
	else
	{
		echo "No Result Found";
	}
				?>
				
                    <div id="myModaltable" class="modal fade" role="dialog">
    <div class="modal-dialog" style="width:50% !important;">
    
      <!-- Modal content-->
      <div class="modal-content" style="height:auto !important;">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Table Settings <?php //echo $formid; ?></h4>
        </div>
        <div class="modal-body">
        
        <form  method="post" enctype="multipart/form-data"  role="form" >
        <div class="form-group">
        <label class="control-label col-sm-3 col-md-3 col-lg-3 col-xs-12">SP Alias</label>
<label class="control-label  col-sm-2 col-md-2 col-lg-2 col-xs-12">Fields</label>
<label class="control-label  col-sm-2 col-md-2 col-lg-2 col-xs-12">Width</label>
<label class="control-label  col-sm-2 col-md-2 col-lg-2 col-xs-12">Align</label>
<label class="control-label  col-sm-3 col-md-3 col-lg-3 col-xs-12">Format</label>


</div>

<?php
foreach($widthpieces as $index=> $width) {
?>
 <div class="form-group" style="clear:both !important;">
 
<label class="control-label  col-sm-3 col-md-3 col-lg-3 col-xs-12"><?php /*echo "Column ".($index+1);*/ echo $dynamic[$index]; ?></label>

<div class="col-sm-2 col-md-2 col-lg-2 col-xs-12">
<input type="text" name="colname[]"  class="form-control input-sm" tabindex="-1" value="<?php if(isset($pieces[$index])) { echo $pieces[$index];} else { echo $dynamic[$index]; }?>" ></div>

<div class=" col-sm-2 col-md-2 col-lg-2 col-xs-12">
<input type="text" name="wid[]"  class="form-control input-sm" value="<?php echo $widthpieces[$index];?>" tabindex="-1" ></div>


<div class=" col-sm-2 col-md-2 col-lg-2">

<select name="ali[]" class="form-control" tabindex="-1">
<option value="16" <?php if($alighnpieces[$index]=='16'){ echo "selected";}?>>Left</option>
<option value="32" <?php if($alighnpieces[$index]=='32'){ echo "selected";}?>>Center</option>
<option value="64" <?php if($alighnpieces[$index]=='64'){ echo "selected";}?>>Right</option>
</select>
</div>

<div class=" col-sm-3 col-md-3 col-lg-3">

<select name="format[]" class="form-control" tabindex="-1">
<?php
if(isset($formatpieces[$index])) 
{
	?>
   
<option value="I" <?php if($formatpieces[$index]=='I'){ echo "selected";}?>>Int</option>
<option value="i" <?php if($formatpieces[$index]=='i'){ echo "selected";}?>>Int(_)</option>
<option value="d" <?php if($formatpieces[$index]=='d'){ echo "selected";}?>>Date(dd/mmm/yy)</option>
<option value="D" <?php if($formatpieces[$index]=='D'){ echo "selected";}?>>Date(dd/mmm/yyyy)</option>
<option value="m" <?php if($formatpieces[$index]=='m'){ echo "selected";}?>>Date(mm-yy)</option>
<option value="M" <?php if($formatpieces[$index]=='M'){ echo "selected";}?>>Date(mmm-yyyy)</option>
<option value="S" <?php if($formatpieces[$index]=='S'){ echo "selected";}?>>String</option>
<option value="C" <?php if($formatpieces[$index]=='C'){ echo "selected";}?>>Cur(0.00)</option>
<option value="c" <?php if($formatpieces[$index]=='c'){ echo "selected";}?>>Cur(0.000)</option>
<option value="T" <?php if($formatpieces[$index]=='T'){ echo "selected";}?>>LongTime</option>
<option value="t" <?php if($formatpieces[$index]=='t'){ echo "selected";}?>>ShortTime</option>
<option value="R" <?php if($formatpieces[$index]=='R'){ echo "selected";}?>>INR Format</option>
<?php
}
else
{
?>
<option value="S">String</option>
<?php
}
?>

</select>

</div>
</div>

<?php
}
?>


<div class="form-group">
<input type="hidden" name="rowid" value="<?php echo $formid; ?>" tabindex="-1">
<input type="submit" name="tablesettings" tabindex="-1" class="btn btn-success" style="float:right !important; margin:10px;" value="SAVE">
</div>
</form>
       
        </div>
        <div class="modal-footer" style="border-top:none !important;">
      
        </div>
      </div>
      
    </div>
  </div>
  
  
  
  
  
  
  <!-- Modal -->
  <div class="modal fade" id="edit_Modal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" tabindex="-1">&times;</button>
          <h4 class="modal-title">Enter Edit Password</h4>
        </div>
        <div class="modal-body">
          
          			<form method='post'>
                    <input type="hidden"  name="edit" tabindex="-1" id="edit_id" >
                    <input type="password" name="edit_password" tabindex="-1" />
                    <input type="submit" name="btnedit" tabindex="-1" value="Submit" class='btn btn-warning btn-md' />
                    </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal" tabindex="-1">Close</button>
        </div>
      </div>
      
    </div>
  </div>
  
  
  
   <!-- Modal -->
  <div class="modal fade" id="remove_Modal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" tabindex="-1">&times;</button>
          <h4 class="modal-title">Enter Remove Password</h4>
        </div>
        <div class="modal-body">
          
          			<form method='post'>
                    <input type="hidden"  name="remove" id="remove_id" tabindex="-1" >
                    <input type="password" name="remove_password" tabindex="-1" />
                    <input type="submit" name="btnremove" value="Submit" tabindex="-1" class='btn btn-warning btn-md' />
                    </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal" tabindex="-1" >Close</button>
        </div>
      </div>
      
    </div>
  </div>
  
  
  
  
  
  