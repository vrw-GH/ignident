<?php
//Find Cron Schedule
$cron_events = _get_cron_array();
?>
<div class="pb-main-wrapper">
    <!-- Main Content  -->
	<div class="pb-page-content pb-schedule-page">
        <!-- Page Specific  -->
        <div class="pb-dashbord-wrap">
			<div class="pb-aside bckup-card">
				<div class="bckup-card-head">
					<h4><?= esc_html__('Currently Running Schedules','autobackups'); ?></h4>
				</div>
				<div class="pb-card-content">
					<div class="pb-table-wrap">
						<div class="pb-table-responsive">
							<table>
								<thead>
									<tr>
									<th><?= esc_html__('Schedules','autobackups'); ?></th>
									<th><?= esc_html__('Next Run(UTC)','autobackups'); ?></th>
									<th><?= esc_html__('Recurrence','autobackups'); ?></th>
									<th><?= esc_html__('Remove','autobackups'); ?></th>
									</tr>
								</thead>
								<tbody>
										<?php 
										// Check if there are any cron events
										$imgDir = AUTO_BACKUP_URL.'/admin/images';
										if (!empty($cron_events)) {
											foreach ($cron_events as $timestamp => $cron_jobs) {
												$currentDate = '';
												$currentDate = date('Y-m-d H:i:s');
												$crondate = date('Y-m-d H:i:s', $timestamp);
												
												foreach ($cron_jobs as $hook => $details) {
													$dateDiff = intval((strtotime($crondate)-strtotime($currentDate))/60);
													$hours = intval($dateDiff/60);
													$minutes = $dateDiff%60;
													$pos = strpos($hook,'backup_sheduled');
													if($pos == 5){
													echo '<tr>';
														echo "<td>$hook </td>";
														echo "<td>" . date('Y-m-d H:i:s', $timestamp) . "</br>" .$hours.' Hours '.$minutes.' Minutes'. "</td>";
														foreach($details as $dt){
														$schedule = $dt['schedule'];
														}
														echo "<td><span class='pb-schedule'>" . $schedule . "</span></td>";
														echo "<td>
																<div class='pb-tb-actions'>
																	<ul>
																		<li class='pb-tooltip-wrap pb-delete'>
																			<div class='pb-tooltip'>Delete</div>
																			<a href='javascript:;' class='autobk-delete-schedule' hook=".esc_attr($hook)."><img src='". esc_url($imgDir."/delete.svg")."' alt='". esc_attr__('delete','autobackup')."'></a>
																		</li>
																	</ul>
																</div>
															  </td>";
													echo '</tr>';
													}
												}
											}
										} else {
											echo "<tr><td  colspan='4' >No cron events are scheduled.</td></tr>";
										}
										?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			 </div>
        </div>
	</div>
</div>