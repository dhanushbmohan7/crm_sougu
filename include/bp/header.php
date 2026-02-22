<?php
$det=$obj->get_details($uid);
$detail=explode('#',$det);
?>

<div id="header" class="tab-pane" style="width:100% !important;  float:left !important; margin-bottom:10px !important; clear:both !important; background-color:#ecf0f5 !important; background-image:url(../css/wavecut.png); margin-bottom:-5px !important;">
<div style="float:left !important; margin-left:10px; margin-top:5px;"><img src="../css/logo.png" /></div>
<div style="float:left; width:300px; text-align:center; font-family:'Palatino Linotype', 'Book Antiqua', Palatino, serif;"><h2><?php echo $detail[1]; ?></h2></div>
<div style="float:right !important; margin-right:20px !important; font-family:'Palatino Linotype', 'Book Antiqua', Palatino, serif;"><h2>Welcome <?php echo $detail[0]; ?></h2></div>
</div>