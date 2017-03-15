
<?php
	include_plugin('jqgrid');
	$vl = new nai_validation();
	$js = new nai_javascript();
	$zd = new nai_zdate();
	$action = getor($_POST['action'],'none');
	// nai_fax_report_set_fake_data();
	if(getor($_GET['download']) == 'true'){
		$id = getor($_GET['id'],14);
		if($id){
			if(!nai_fax_report_exist($id)){
				echo 'Unknown ID';
			}else{
				$file = nai_fax_report_get_file_address($id);
				nai_fax_report_make_as_read($id);
				nai_file_download($file);
				
			}
		}else{
			echo 'No ID';
		}
		exit();
	}

	$fax_dev = getor($_GET['device']) ;
	if($fax_dev) $fax_dev_ = nai_fax_get_fax_device( $fax_dev  );
	

	
	
	switch($action){
		case 'show':
			$type = getor($_POST['radio-type']);
			$from = ($type=='1')?
						(getor($_POST['text-infrom']))
					:
						($fax_dev?$fax_dev_['number']:getor($_POST['select-outfrom']))
					;
			$to   = ($type=='1')?
						($fax_dev?$fax_dev_['number']:getor($_POST['select-into']))
					:
						(getor($_POST['text-outto']));
			$status = ($type=='1')?(getor($_POST['checkbox-readsts'])):(getor($_POST['checkbox-sendsts']));
			$datefrom = getor($_POST['text-datefrom']);
			$dateto   = getor($_POST['text-dateto']);
			
			$query = nai_fax_report_make_query($type,$from,$to,$status,$datefrom,$dateto,false);
			//show_query($query);
			$hexquery = nai_query_encode( $query );

			
			$js->return_form($_POST);
			break;
	}
	
	
?>


<link href="modules/faxreport/css/style.css" rel="stylesheet" type="text/css">
<script>
	$(function(){
	
	<?php 
		if($action=='show'){
	?>
		var faxReportSendStatus = [
			{color:'27B16F',text:'ارسال‌شده'},
			{color:'FB8B02',text:'درحال‌ارسال'},
			{color:'FB0F02',text:'لغو‌شده'},
			{color:'5A5A5A',text:'ثبت‌برای‌ارسال'}
		];
		var faxReportRead = [
			{color:'gray'},
			{color:'green'}
		]
		
		naiGrid({
			elem: '#faxreportgrid',
			url: '?nai_module=faxreport&grid=true&faxtype=<?php echo $type; ?>',
			query: '<?php echo $hexquery; ?>',
			cols: [
				{model: 'from', name: 'از شماره'},
				{model: 'to', name: 'به شماره'},
			<?php
				if($type=='0'){ //out
			?>
				{ model: {name:'sendstatus', formatter: function(e){
					return "<span style=\"color:#"+ faxReportSendStatus[e-1].color +";\">"+ faxReportSendStatus[e-1].text+ "</span>";
				}}, name: 'وضعیت'},
				{model: 'created_time', name: 'تاریخ‌اقدام'},
				{model: 'sendtime', name: 'تاریخ‌ثبت'},
				{model: 'sentime', name: 'تاریخ‌ارسال'},
			<?php
				} else { //in
			?>
				{ model: {name:'read', formatter: function(e){
					return "<span class='icon icon-check-alt "+ faxReportRead[parseInt(e)].color+ " s16 gray'></span>";
				}}, name: 'خوانده‌شده'},
				{model: 'created_time', name: 'تاریخ‌دریافت'},
			<?php
				}
			?>
				{model: {name:'fileaddress',formatter: function(e){
					return '<a href="config.php?display=faxreport&download=true&id='+e+'"> دانلود </a>';
				}}, name: 'فایل'}
			],
			sortname: 'created_time',
			excel: true
		});
	<?php 
		}
	?>

		$('[name=radio-type]').change(function(){
			var val = $(this).val();
			$('[data-radio-type]').hide();
			$('[data-radio-type="'+val+'"]').show();
		});
		$('[name=radio-type]:checked').change();
	
	});
</script>

<h2>فکس ها</h2>

<form method=post>
<table class="nai_table faxreport_setting_table" width="100%">
		<tr class="title">
			<td colspan="2"> <h5> تنظیمات عمومی <h5> <hr> </td>
		</tr>
	<tr>
		<td>نوع فکس</td>
		<td>
			<label><input name=radio-type type=radio value='1' <?php if($action!='show') echo 'checked'; ?> > دریافتی </label>
			<label><input name=radio-type type=radio value='0'	   > ارسالی </label>
		</td>
	</tr>
	<tr data-radio-type='0'>
		<td>از دستگاه</td>
		<td>
			<?php if(!$fax_dev){ ?>
			<select name=select-outfrom class="chosen">
				<?php
					$devs = nai_fax_get_fax_devices();
					foreach($devs as $dev){
						echo "<option value='$dev[id]'> $dev[name] - $dev[number]</otion>";
					}
				
				?>
			</select>
			<?php } else { echo "$fax_dev_[name] - $fax_dev_[number]"; } ?>
		</td>
	</tr>
	<tr data-radio-type='0'>
		<td>به شماره</td>
		<td>
			<input name=text-outto type=text   pattern="<?php echo $vl->pattern('number'); ?>"></input>
		</td>
	</tr>
	<tr data-radio-type='0'>
		<td>وضعیت</td>
		<td>
			<label style="color:#27B16F;"><input name="checkbox-sendsts[0]"  value='1' type=checkbox checked> ارسال شده </label>
			<label style="color:#FB8B02;"><input name="checkbox-sendsts[1]"  value='2' type=checkbox checked> در حال ارسال </label>
			<label style="color:#FB0F02;"><input name="checkbox-sendsts[2]"  value='3' type=checkbox checked> لغو شده </label>
			<label style="color:#5A5A5A;"><input name="checkbox-sendsts[3]"  value='4' type=checkbox checked> ثبت برای ارسال </label>
		</td>
	</tr>
	<tr data-radio-type='0'></tr>
	
	<tr data-radio-type='1'>
		<td>از شماره</td>
		<td>
			<input name=text-infrom type=text  pattern="<?php echo $vl->pattern('number'); ?>"></input>
		</td>
	</tr>
	<tr data-radio-type='1'>
		<td>به دستگاه</td>
		<td>
			<?php if(!$fax_dev){ ?>
			<select name=select-into class="chosen">
				<?php
					$devs = nai_fax_get_fax_devices();
					foreach($devs as $dev){
						echo "<option value='$dev[id]'> $dev[name] - $dev[number]</otion>";
					}
				
				?>
			</select>
			<?php } else { echo "$fax_dev_[name] - $fax_dev_[number]"; } ?>
		</td>
	</tr>
	<tr data-radio-type='1'>
		<td>وضعیت</td>
		<td>
			<label><input name="checkbox-readsts[0]"  value='0'	type=checkbox checked> جدید </label>
			<label><input name="checkbox-readsts[1]"  value='1' type=checkbox checked> خوانده شده </label>
		</td>
	</tr>
	<tr>
		<td>
		 از تاریخ 
		</td>
		<td>
			<input type=datepicker name=text-datefrom ></input>
		</td>
	</tr>
	
	<tr>
		<td>
		 تا تاریخ 
		</td>
		<td>
			<input type=datepicker name=text-dateto ></input>
		</td>
	</tr>
	<tr class="title">
		<td colspan="2"><button type=submit name=action value=show>نمایش</button></td>
	</tr>
</table>


</form>



<div id="chart" class="nai_chart" style="width:860px;margin-top:10px;">

</div>
<table id="faxreportgrid" class="nai_grid nai_table"></table>

<div style="margin: 10px auto;">
	<table id="list" class="nai_grid nai_table"><tr><td></td></tr></table> 
	<div id="pager" class="nai_grid"></div>
</div>
