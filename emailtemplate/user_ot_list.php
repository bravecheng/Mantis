<div style="font-family:Verdana;">Hi <?php echo $addtionalData['username']?>,

1.	Please print & fill "OT Application Form" according to the following information and submit it to <b>Gavin Cao</b> for OT approval. 
2.	Upon approval, please submit the completed form to <b>HR</b>:

<table border="0" cellpadding="3" cellspacing="1"  style="background-color: #000000;width:500px"><tr bgcolor='#ffffff'><td>Name</td><td>Date</td><td>Hour(s)</td><td>Type</td></tr><?php foreach ($arrMainData as $arrData){ if($arrData["employeeId"] == $addtionalData["employeeId"]){?><tr bgcolor='#ffffff'><td><?php echo $arrData['username'];?></td><td><?php echo $arrData['date'];?></td><td><?php echo $arrData['time'];?></td><td><?php echo $arrData['type'];?></td></tr><?php }}?></table>

<span style="color:red;">Note:  upon receiving this notification, you must submit the “OT Application Form” to Gavin Cao within 3 business days.  Any OT entries past the 3-day-deadline will be voided.</span>

For further inquiries, please do not hesitate to contact <b>Elsie Gao</b> at <a href="mailto:elsie.gao@expacta.com.cn">elsie.gao@expacta.com.cn</a>.


<b>Expacta OT System</b></div>
