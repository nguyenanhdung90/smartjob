<?php
/**
 Template Name:page-mail
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other "pages" on your WordPress site will use a different template.
 *
 * @package WordPress
 * @subpackage
 * @since
 */
 /*
global $wpdb;
$fivesdrafts=$wpdb->get_results( "SELECT * FROM wp_mail_check where post_id =2341  " );
//echo  $wpdb->num_rows . 'Rows Found';
if($wpdb->num_rows > 0)echo "ds";
if($wpdb->num_rows == 0)echo "ds dfds";
*/
//---------------------------------------------------------------------------------------------------------------------------------
/*
$incre=0;
foreach ( $fivesdrafts as $fivesdraft ) 
{
	$mang_com[$incre]=$fivesdraft;
	$incre++;	
}
echo count($mang_com);echo "<br>";
*/
/*

for ($x = 0; $x <= 0; $x++) {
   // echo $mang_com[$x]->display_name."<br>";
	$company_editor_id=$mang_com[$x]->ID;
	$fivesdrafts_post=$wpdb->get_results( "SELECT * FROM wp_posts where company_editor_id = '".$company_editor_id."'  " );
	$job_mail_cu_the=", ";
	$titel_job="SmartJob free posted your job on our website";
	foreach ( $fivesdrafts_post as $fivesdraft_post ) 
	{
		$job_mail_cu_the=$job_mail_cu_the.'<a class="daria-goto-anchor" data-orig-href="http://smartjob.vn/job/'.$fivesdraft_post->post_name.'" data-vdir-href="https://mail.yandex.com/re.jsx?uid=1130000019427938&amp;h=a,Nd-Azw4z2EN0Qp0fT_A7hQ&amp;l=aHR0cDovL3NtYXJ0am9iLnZuL2pvYi9qb2ItZHVuLXVuZw" target="_blank" style="color:red;" href="http://smartjob.vn/job/'.$fivesdraft_post->post_name.'">'.$fivesdraft_post->post_title.'</a>, ';
	}
	$content='
			<div class="ii gt m1513903d206bec5f adP adO" id=":nl">
				<div style="overflow: hidden;" class="a3s" id=":mr">
					<div class="adM">
					</div>
					<div style="font-family:Arial,sans-serif;font-size:0.9em;margin:0;padding:0;color:#222222">
						<div class="adM">

						</div>
						<table cellspacing="0" cellpadding="0" width="100%">
							<tbody>
							<tr style="background:#2E4C6B;height:63px;vertical-align:middle">
								<td align="left" style="padding:10px 5px 10px 20px;width:20%;min-width:300px;display:inline-block">                                              
									<div style="font-weight:bold;font-size:29px;height:35px"><span style="color:white">SMART</span><span style="color:#e63a35">JOB</span></div>
									<div style="color:white;font-size:17px">For the successful life</div>						
								</td>
								<td align="left" style="padding:10px 20px 10px 5px;min-width:300px;display:inline-block">
									<span style="color:#b0b0b0"></span>
								</td>
							</tr>
							<tr>
								<td style="background:#ffffff;color:#222222;line-height:18px;padding:10px 20px;font-size:17px" colspan="2">
									<p>Dear '.$mang_com[$x]->display_name.', </p>
									<p>
									We’ve posted your job '.$job_mail_cu_the.' on <a target="_blank" class="daria-goto-anchor" data-orig-href="http://smartjob.vn/" data-vdir-href="https://mail.yandex.com/re.jsx?uid=1130000019427938&amp;h=a,7rsxy2PsI3wybjHivuB6iw&amp;l=aHR0cDovL3NtYXJ0am9iLnZuLw" href="http://smartjob.vn/" style="color:#15c;"> smartjob.vn</a>
									</p>
									<p>
									Any information you want, please contact with us. 
									</p>
									<p>
									Phone : <span class="wmi-callto">0462944447</span> <br>
									Email : <a data-params="new_window&amp;url=#compose/mailto=contact@smartjob.vn" data-action="common.go" class="daria-action" href="mailto:contact@smartjob.vn">contact@smartjob.vn</a>
									</p>
									<p>
									Thank you and welcome to SmartJob - Mạng tuyển dụng hàng đầu Việt Nam.
									</p>
								</td>
							</tr>
							<tr>
								<td style="background:#f2f4f7;padding:10px 20px;color:#666" colspan="2">
									<table cellspacing="0" cellpadding="0" width="100%">
										<tbody>
										<tr>
											<td style="vertical-align:top;text-align:left;width:50%">&copy; Copyright 
											<a style="color:#15c" target="_blank" href="http://smartjob.vn/">SMARTJOB</a></td>
											<td style="text-align:right;width:50%">SmartJob - Mạng tuyển dụng hàng đầu Việt Nam <br>
												<a style="color:#15c" target="_blank" href="mailto:contact@smartjob.vn">contact@smartjob.vn</a> <br></td>
										</tr>
										</tbody>
									</table>
								</td>
							</tr>
							</tbody>
						</table>
						<div class="yj6qo"></div>
						<div class="adL">
						</div>
					</div>
					<div class="adL">
					</div>
				</div>
			</div>
	';
	$mail_com=$mang_com[$x]->user_email;
	$if12 = wp_mail( $mail_com,$titel_job,$content );
	if($if12)echo $mang_com[$x]->ID."--".$mang_com[$x]->user_email."<br>";
}
*/
/*
foreach ( $fivesdrafts as $fivesdraft ) 
{
	echo $fivesdraft->ID;echo "<br>";
	$company_editor_id=$fivesdraft->ID;
	$mail_com=$fivesdraft->user_email;
	$titel_job="Smartjob.vn thân gửi ".$fivesdraft->display_name;
	$fivesdrafts_post=$wpdb->get_results( "SELECT * FROM wp_posts where company_editor_id = '".$company_editor_id."'  " );
	$increat=0;
	$job_mail_cu_the=", ";
	foreach ( $fivesdrafts_post as $fivesdraft_post ) 
	{
		//echo "--".$fivesdraft_post->post_name;echo "<br>";
$job_mail_cu_the=$job_mail_cu_the.'<a class="daria-goto-anchor" data-orig-href="http://smartjob.vn/job/'.$fivesdraft_post->post_name.'" data-vdir-href="https://mail.yandex.com/re.jsx?uid=1130000019427938&amp;h=a,Nd-Azw4z2EN0Qp0fT_A7hQ&amp;l=aHR0cDovL3NtYXJ0am9iLnZuL2pvYi9qb2ItZHVuLXVuZw" target="_blank" style="color:red;" href="http://smartjob.vn/job/'.$fivesdraft_post->post_name.'">'.$fivesdraft_post->post_title.'</a> ,';

	}
	$content='
			<div class="ii gt m1513903d206bec5f adP adO" id=":nl">
				<div style="overflow: hidden;" class="a3s" id=":mr">
					<div class="adM">
					</div>
					<div style="font-family:Arial,sans-serif;font-size:0.9em;margin:0;padding:0;color:#222222">
						<div class="adM">

						</div>
						<table cellspacing="0" cellpadding="0" width="100%">
							<tbody>
							<tr style="background:#2E4C6B;height:63px;vertical-align:middle">
								<td align="left" style="padding:10px 5px 10px 20px;width:20%;min-width:300px;display:inline-block">                                              
									<div style="font-weight:bold;font-size:29px;height:35px"><span style="color:white">SMART</span><span style="color:#e63a35">JOB</span></div>
									<div style="color:white;font-size:17px">For the successful life</div>						
								</td>
								<td align="left" style="padding:10px 20px 10px 5px;min-width:300px;display:inline-block">
									<span style="color:#b0b0b0"></span>
								</td>
							</tr>
							<tr>
								<td style="background:#ffffff;color:#222222;line-height:18px;padding:10px 20px;font-size:17px" colspan="2">
									<p>Dear '.$fivesdraft->display_name.', </p>
									<p>
									We’ve posted your job '.$job_mail_cu_the.' on <a target="_blank" class="daria-goto-anchor" data-orig-href="http://smartjob.vn/" data-vdir-href="https://mail.yandex.com/re.jsx?uid=1130000019427938&amp;h=a,7rsxy2PsI3wybjHivuB6iw&amp;l=aHR0cDovL3NtYXJ0am9iLnZuLw" href="http://smartjob.vn/" style="color:#15c;"> smartjob.vn</a>
									</p>
									<p>
									Any information you want, please contact with us. 
									</p>
									<p>
									Phone : <span class="wmi-callto">0462944447</span> <br>
									Email : <a data-params="new_window&amp;url=#compose/mailto=contact@smartjob.vn" data-action="common.go" class="daria-action" href="mailto:contact@smartjob.vn">contact@smartjob.vn</a>
									</p>
									<p>
									Thank you and welcome to SmartJob - Mạng tuyển dụng hàng đầu Việt Nam.
									</p>
								</td>
							</tr>
							<tr>
								<td style="background:#f2f4f7;padding:10px 20px;color:#666" colspan="2">
									<table cellspacing="0" cellpadding="0" width="100%">
										<tbody>
										<tr>
											<td style="vertical-align:top;text-align:left;width:50%">&copy; Copyright 
											<a style="color:#15c" target="_blank" href="http://smartjob.vn/">SMARTJOB</a></td>
											<td style="text-align:right;width:50%">SmartJob - Mạng tuyển dụng hàng đầu Việt Nam <br>
												<a style="color:#15c" target="_blank" href="mailto:contact@smartjob.vn">contact@smartjob.vn</a> <br></td>
										</tr>
										</tbody>
									</table>
								</td>
							</tr>
							</tbody>
						</table>
						<div class="yj6qo"></div>
						<div class="adL">
						</div>
					</div>
					<div class="adL">
					</div>
				</div>
			</div>
	';
	echo $content."<br>";
	
//wp_mail( $mail_com,$titel_job,$content, );
}
 */
/*
$headers = array('Content-Type: text/html; charset=UTF-8','From: Smartjob <contact@smartjob.vn>');


wp_mail( 'nadungnd@gmail.com', 'chao ban dung  4 pm 57',$content,$headers );
*/

//----------------------------------------------
/*

		$header	=	
		'<html class="no-js" lang="en">
			<body style="margin: 0px; font-family: Arial,sans-serif; font-size: 13px; line-height: 1.3;">
				<style>
					.job_list {
						color : #000;
					}
				</style>
				<div style="margin: 0px auto; width:100%; border: 1px solid #f2f4f7">

					<table width="100%" cellspacing="0" cellpadding="0">
						<tr style="background-color: #2e4c6b; display:block; padding:10px 0px; vertical-align: middle; box-shadow: 0 2px 0 2px #E3E3E3;">
							<td align="left" style="padding:10px 5px 10px 20px;width:43%;min-width:300px;display:inline-block">                                              
								<div style="font-weight:bold;font-size:29px;height:35px"><span style="color:white">SMART</span><span style="color:#e63a35">JOB</span></div>
								<div style="color:white;font-size:17px">For the successful life</div>						
							</td>
							<td align="left" style="padding:10px 10px 10px 0px;width:50%;display:inline-block;text-align:right">
								<a href="http://smartjob.vn/" target="_blank" style="display: block; position: relative;text-decoration: none; padding: 0; height: 40px; line-height: 40px;">
									<span style="color:white; font-size: 16px; ">
										 	Click here for view more Job
									</span>						
								</a>
							</td>									
						</tr>						
						<tr>
							<td colspan="3" style="padding: 10px 20px">
								<table>
									<tr>
										<td colspan="2" style="line-height : 26px ;font-size : 24px; color: #5c5c5c; padding-bottom: 10px; font-weight: normal; font-family :Arial,sans-serif;">
										Re-discover your potentials. Re-vision your future. Meet Success. Let smartjob.vn take you there !
										</td>
									</tr>
                                    <tr><td style="padding:8px 10px 8px 0;">
										<a style="display:block;padding:5px;height:70px;border-radius:3px;border-bottom:2px solid #E9E9E9;" class="daria-goto-anchor" target="_blank">
										<img width="70" height="70" alt="" title="" src="https://resize.yandex.net/mailservice?url=http%3A%2F%2Fsmartjob.vn%2Fwp-content%2Fuploads%2F2016%2F04%2FTinhvan-Group-200x74.jpg&amp;proxy=yes&amp;key=2c2c470fd595f9a6a6c34d397cee7553"></a>
									</td>
									<td valign="top" style="padding:10px 0;">
										<a style="font-size:15px;font-family:Helvetica,san-serif;text-decoration:none;display:block;color:#5c5c5c;font-weight:300;margin-bottom:10px;text-transform:uppercase;" href="http://smartjob.vn/job/tuyen-30-developerproject-manager-up-to-1500/" data-vdir-href="https://mail.yandex.com/re.jsx?uid=1130000019427938&amp;h=a,R0JE99Ges__9n8AP1z0TeA&amp;l=aHR0cDovL3NtYXJ0am9iLnZuL2pvYi90dXllbi0zMC1kZXZlbG9wZXJwcm9qZWN0LW1hbmFnZXItdXAtdG8tMTUwMC8" data-orig-href="http://smartjob.vn/job/tuyen-30-developerproject-manager-up-to-1500/" class="daria-goto-anchor" target="_blank">Tuyển 30 Developer/Project Manager up to 1500$</a>
										<div style="color:#909090;font-family:Helvetica,san-serif;font-size:12px;">
											Mô tả công việc Do nhu cầu mở rộng dự án Công ty Cổ phần Xuất khẩu phần mềm Tinh Vân tuyển dụng nhiều nhân sự làm Lập trình viên và Quản lý dự án tại trụ sở Hà Nội, chi nhánh Hồ Chí Minh, Chi nhánh Đà Nẵng, Quốc tế cụ thể tuyển các […]
										</div></td>
									</tr>                
									<tr><td style="padding:8px 10px 8px 0;">
										<a style="display:block;padding:5px;height:70px;border-radius:3px;border-bottom:2px solid #E9E9E9;" class="daria-goto-anchor" target="_blank">
										<img width="70" height="70" alt="" title="" src="https://resize.yandex.net/mailservice?url=http%3A%2F%2Fsmartjob.vn%2Fwp-content%2Fuploads%2F2016%2F04%2FTinhvan-Group-200x74.jpg&amp;proxy=yes&amp;key=2c2c470fd595f9a6a6c34d397cee7553"></a>
									</td>
									<td valign="top" style="padding:10px 0;">
										<a style="font-size:15px;font-family:Helvetica,san-serif;text-decoration:none;display:block;color:#5c5c5c;font-weight:300;margin-bottom:10px;text-transform:uppercase;" href="http://smartjob.vn/job/tuyen-30-developerproject-manager-up-to-1500/" data-vdir-href="https://mail.yandex.com/re.jsx?uid=1130000019427938&amp;h=a,R0JE99Ges__9n8AP1z0TeA&amp;l=aHR0cDovL3NtYXJ0am9iLnZuL2pvYi90dXllbi0zMC1kZXZlbG9wZXJwcm9qZWN0LW1hbmFnZXItdXAtdG8tMTUwMC8" data-orig-href="http://smartjob.vn/job/tuyen-30-developerproject-manager-up-to-1500/" class="daria-goto-anchor" target="_blank">Tuyển 30 Developer/Project Manager up to 1500$</a>
										<div style="color:#909090;font-family:Helvetica,san-serif;font-size:12px;">
											Mô tả công việc Do nhu cầu mở rộng dự án Công ty Cổ phần Xuất khẩu phần mềm Tinh Vân tuyển dụng nhiều nhân sự làm Lập trình viên và Quản lý dự án tại trụ sở Hà Nội, chi nhánh Hồ Chí Minh, Chi nhánh Đà Nẵng, Quốc tế cụ thể tuyển các […]
										</div></td>
									</tr>
								';
             $footer	=	'</table>
							</td>
								</tr>
								<tr style="padding: 10px 20px; color: #909090; height: 89px; background-repeat: repeat-x; background-color:#f7f7f7;">
									<td colspan="3">
										<table width="100%" cellspacing="0" cellpadding="0">
											<tr>
												<td style="text-align: left; padding: 10px 20px;">
													<table>
														<tr>
															<td>© Copyright <a style="color:#15c" target="_blank" href="http://smartjob.vn/">SMARTJOB</a></td>
														</tr>
														<tr>
															<td style="text-align:left;"><a href="http://smartjob.vn/unsubscribe">Unsubscribe</a>
															</td>
														</tr>						
													</table>
												</td>
												<td style="text-align: right; padding: 10px 20px;">
													<a href="mailto:contact@smartjob.vn" class="daria-action" data-action="common.go" data-params="new_window&amp;url=#compose/mailto=contact@smartjob.vn">contact@smartjob.vn</a>
												</td>
											</tr>
										</table>
									</td>
									
								</tr>
							</table>
							
						</div>
						
					</body>
					</html>';

			$mail_alert_message = $header.$footer;	
			wp_mail( 'dungna@dcv.vn', 'mail_alert_message',$mail_alert_message );
			
			
			$de= get_post(3060);
			echo $de->post_author;
			$role=get_userdata($de->post_author);
			$ff=$role->roles;
			echo $ff[0];
						$do=WP_CONTENT_DIR.'/uploads/2016/03/logo_smartjob1.png';
			echo $do;
			$dc = array( WP_CONTENT_DIR . '/uploads/2016/03/logo_smartjob1.png');
			wp_mail( 'dungna@dcv.vn', 'mail tét cua dung','noi dung','',$dc );
						echo get_attached_file(3201);
			et_update_post(array('ID' => 3201, 'post_parent' => 3202));
			*/
$args = array(
	'post_parent' => 3333,
	'post_type'   => 'attachment', 
	'numberposts' => -1	
); 

$attachments = get_children( $args, ARRAY_A );

		foreach ( $attachments as $attachment ) {
			
			echo $attachment[guid];
		}
