<?php
use \packages\base\translator;
use \packages\userpanel;
$this->the_header();
?>
<div class="row">
	<div class="col-xs-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-university"></i><?php echo translator::trans("department_edit"); ?>
				<div class="panel-tools">
					<a class="btn btn-xs btn-link panel-collapse collapses" href="#"></a>
				</div>
			</div>
			<div class="panel-body">
				<form id="settings-departmetns-management" class="form-horizontal" action="<?php echo userpanel\url("settings/departments/edit/".$this->department->id); ?>" method="post">
					<?php
						$this->setHorizontalForm('sm-2','sm-5');
						$this->createField(
						array(
							'name' => 'title',
							'label' => translator::trans("department.title")
						));
						$this->horizontal_form = false;
					?>
					<div class="col-md-12">
						<table class="table sliders table-striped">
							<thead>
								<tr>
									<th></th>
									<th><?php echo translator::trans("days"); ?></th>
									<th><?php echo translator::trans("worktimes"); ?></th>
									<th><?php echo translator::trans("un_worktimes.message"); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($this->sortedDays() as $day){ ?>

								<tr>
									<td class="center" style="width: 10px;">
										<?php
										$this->createField(array(
											'type' => 'hidden',
											'name' => "day[{$day->day}][worktime][start]"
										));
										$this->createField(array(
											'type' => 'hidden',
											'name' => "day[{$day->day}][worktime][end]"
										));
										?>
										<div class="checkbox-table">
										<?php
										$this->createField(array(
											'type' => 'checkbox',
											'name' => "day[{$day->day}][enable]",
											'options' => array(
												array(
													'value' => true,
													'class' => 'flat-grey'
												)
											)
										));

										?>
										</div>
									</td>
									<td><?php echo($this->getTranslatDays($day->day)); ?></td>
									<td>
										<div data-day="<?php echo $day->day; ?>" class="slider"></div>
									</td>
									<td>
										<?php
										$this->createField(array(
											'type' => 'textarea',
											'name' => "day[{$day->day}][message]",
										));
										?>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
					<hr>
					<p>
						<a href="<?php echo userpanel\url("settings/departments"); ?>"class="btn btn-light-grey"><i class="fa fa-chevron-circle-right"></i> <?php echo translator::trans('return'); ?></a>
						<button type="submit" class="btn btn-teal"><i class="fa fa-check-square-o"></i> <?php echo translator::trans("edit"); ?></button>
					</p>
				</form>
			</div>
		</div>
	</div>
</div>
<?php
$this->the_footer();
