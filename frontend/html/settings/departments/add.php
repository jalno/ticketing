<?php
use packages\base\Translator;
use packages\userpanel;

$this->the_header();
?>
<div class="row">
	<div class="col-xs-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-university"></i><?php echo t('department_add'); ?>
				<div class="panel-tools">
					<a class="btn btn-xs btn-link panel-collapse collapses" href="#"></a>
				</div>
			</div>
			<div class="panel-body">
				<form id="settings-departmetns-management" action="<?php echo userpanel\url('settings/departments/add'); ?>" method="post">
					<div class="row">
						<div class="col-md-5 col-sm-5 col-xs-12 right-side-inputs">
						<?php
                            $this->createField([
                                'name' => 'title',
                                'label' => t('department.title'),
                            ]);
$this->createField([
    'name' => 'status',
    'type' => 'select',
    'label' => t('ticketing.departments.status'),
    'options' => $this->getDepartmentStatusForSelect(),
]);
$this->createField([
    'name' => 'products',
    'type' => 'hidden',
]);
$this->createField([
    'name' => 'products-select',
    'type' => 'select',
    'multiple' => true,
    'label' => t('ticketing.departments.products'),
    'options' => $this->getProductsForSelect(),
]);
$this->createField([
    'type' => 'checkbox',
    'name' => 'mandatory_choose_product',
    'options' => [
        [
            'label' => t('ticketing.departments.mandatory_choose_product'),
            'value' => '1',
        ],
    ],
]);
?>
						</div>
						<div class="col-md-7 col-sm-7 col-xs-12">
							<div class="row">
								<div class="col-xs-12">
									<div class="panel panel-white panel-users">
										<div class="panel-heading">
											<i class="fa fa-users"></i><?php echo t('ticketing.department.users'); ?>
											<div class="panel-tools">
												<a class="btn btn-xs btn-link panel-collapse collapses" href="#"></a>
											</div>
										</div>
										<div class="panel-body panel-scroll">
											<?php foreach ($this->getUsers() as $user) {
											    $this->createField([
											        'type' => 'checkbox',
											        'name' => "users[{$user->id}]",
											        'options' => [
											            [
											                'label' => $user->getFullName(),
											                'value' => $user->id,
											            ],
											        ],
											    ]);
											} ?>
										</div>
										<div class="panel-footer">
											<div class="row">
												<div class="col-xs-12">
												<?php $this->createField([
												    'type' => 'checkbox',
												    'name' => 'allUsers',
												    'options' => [
												        [
												            'label' => t('ticketing.departments.operators.all_operators'),
												            'value' => 'all',
												        ],
												    ],
												]); ?>
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
									<i class="fa fa-calendar"></i><?php echo t('ticketing.department.day.works'); ?>
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
													<th><?php echo t('days'); ?></th>
													<th><?php echo t('worktimes'); ?></th>
													<th><?php echo t('un_worktimes.message'); ?></th>
												</tr>
											</thead>
											<tbody>
												<?php foreach ($this->sortedDays() as $day) { ?>
												<tr>
													<td class="center" style="width: 10px;">
														<?php
											            $this->createField([
											                'type' => 'hidden',
											                'name' => "day[{$day['day']}][worktime][start]",
											                'value' => 0,
											            ]);
												    $this->createField([
												        'type' => 'hidden',
												        'name' => "day[{$day['day']}][worktime][end]",
												        'value' => 0,
												    ]);
												    ?>
														<div class="checkbox-table">
															<div class="checkbox">
																<label class="">
																	<input name="<?php echo "day[{$day['day']}][enable]"; ?>" value="1" type="checkbox">
																</label>
															</div>
														</div>
													</td>
													<td><?php echo $this->getTranslatDays($day['day']); ?></td>
													<td style="min-width: 200px;">
														<input class="slider" data-day="<?php echo $day['day']; ?>">
													</td>
													<td>
														<?php
												    $this->createField([
												        'type' => 'textarea',
												        'name' => "day[{$day['day']}][message]",
												    ]);
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
						<a href="<?php echo userpanel\url('settings/departments'); ?>"class="btn btn-light-grey"><i class="fa fa-chevron-circle-<?php echo Translator::isRTL() ? 'right' : 'left'; ?>"></i> <?php echo t('return'); ?></a>
						<button type="submit" class="btn btn-success"><i class="fa fa-check-square-o"></i> <?php echo t('add'); ?></button>
					</p>
				</form>
			</div>
		</div>
	</div>
</div>
<?php
$this->the_footer();
