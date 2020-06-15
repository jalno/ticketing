<?php
use packages\base\translator;
use packages\userpanel;
$this->the_header();
?>
<div class="row">
	<div class="col-xs-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-university"></i><?php echo translator::trans("department_add"); ?>
				<div class="panel-tools">
					<a class="btn btn-xs btn-link panel-collapse collapses" href="#"></a>
				</div>
			</div>
			<div class="panel-body">
				<form id="settings-departmetns-management" action="<?php echo userpanel\url("settings/departments/add"); ?>" method="post">
					<div class="row">
						<div class="col-md-5 col-sm-5 col-xs-12 right-side-inputs">
						<?php
							$this->createField(array(
								'name' => 'title',
								'label' => t("department.title")
							));
							$this->createField(array(
								'name' => 'status',
								'type' => 'select',
								'label' => t("ticketing.departments.status"),
								'options' => $this->getDepartmentStatusForSelect(),
							));
							$this->createField(array(
								'name' => 'products',
								'type' => 'hidden',
							));
							$this->createField(array(
								'name' => 'products-select',
								'type' => 'select',
								'multiple' => true,
								'label' => t('ticketing.departments.products'),
								'options' => $this->getProductsForSelect(),
							));
							$this->createField(array(
								'type' => 'checkbox',
								'name' => 'mandatory_choose_product',
								'options' => array(
									array(
										'label' => t('ticketing.departments.mandatory_choose_product'),
										'value' => '1',
									),
								),
							));
						?>
						</div>
						<div class="col-md-7 col-sm-7 col-xs-12">
							<div class="row">
								<div class="col-xs-12">
									<div class="panel panel-white panel-users">
										<div class="panel-heading">
											<i class="fa fa-users"></i><?php echo translator::trans("ticketing.department.users"); ?>
											<div class="panel-tools">
												<a class="btn btn-xs btn-link panel-collapse collapses" href="#"></a>
											</div>
										</div>
										<div class="panel-body panel-scroll">
											<?php foreach ($this->getUsers() as $user) {
												$this->createField(array(
													"type" => "checkbox",
													"name" => "users[{$user->id}]",
													"options" => array(
														array(
															"label" => $user->getFullName(),
															"value" => $user->id,
														),
													),
												));
											} ?>
										</div>
										<div class="panel-footer">
											<div class="row">
												<div class="col-xs-12">
												<?php $this->createField(array(
													"type" => "checkbox",
													"name" => "allUsers",
													"options" => array(
														array(
															"label" => t("ticketing.departments.operators.all_operators"),
															"value" => "all",
														),
													),
												)); ?>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<div class="panel panel-white panel-day-works">
								<div class="panel-heading">
									<i class="fa fa-calendar"></i><?php echo translator::trans("ticketing.department.day.works"); ?>
									<div class="panel-tools">
										<a class="btn btn-xs btn-link panel-collapse collapses" href="#"></a>
									</div>
								</div>
								<div class="panel-body">
									<div class="table-responsive">
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
															'name' => "day[{$day['day']}][worktime][start]",
															'value' => 0
														));
														$this->createField(array(
															'type' => 'hidden',
															'name' => "day[{$day['day']}][worktime][end]",
															'value' => 0
														));
														?>
														<div class="checkbox-table">
															<div class="checkbox">
																<label class="">
																	<input name="<?php echo "day[{$day['day']}][enable]"; ?>" value="1" type="checkbox">
																</label>
															</div>
														</div>
													</td>
													<td><?php echo($this->getTranslatDays($day['day'])); ?></td>
													<td style="min-width: 200px;">
														<div data-day="<?php echo $day['day']; ?>" class="slider"></div>
													</td>
													<td>
														<?php
														$this->createField(array(
															'type' => 'textarea',
															'name' => "day[{$day['day']}][message]",
														));
														?>
													</td>
												</tr>
												<?php } ?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
					<p>
						<a href="<?php echo userpanel\url("settings/departments"); ?>"class="btn btn-light-grey"><i class="fa fa-chevron-circle-<?php echo (bool) Translator::getLang()->isRTL() ? "right" : "left"; ?>"></i> <?php echo translator::trans('return'); ?></a>
						<button type="submit" class="btn btn-success"><i class="fa fa-check-square-o"></i> <?php echo translator::trans("add"); ?></button>
					</p>
				</form>
			</div>
		</div>
	</div>
</div>
<?php
$this->the_footer();
