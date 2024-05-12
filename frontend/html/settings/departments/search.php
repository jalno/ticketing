<?php
use packages\userpanel;

$this->the_header();
?>
<div class="row">
	<div class="col-xs-12">
	<?php if (!empty($this->getDepartments())) { ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-university"></i> <?php echo t('departments'); ?>
				<div class="panel-tools">
					<a class="btn btn-xs btn-link tooltips" title="<?php echo t('search'); ?>" href="#search" data-toggle="modal" data-original-title=""><i class="fa fa-search"></i></a>
					<?php if ($this->canAdd) { ?>
					<a class="btn btn-xs btn-link tooltips" title="<?php echo t('add'); ?>" href="<?php echo userpanel\url('settings/departments/add'); ?>"><i class="fa fa-plus"></i></a>
					<?php } ?>
					<a class="btn btn-xs btn-link panel-collapse collapses"></a>
				</div>
			</div>
			<div class="panel-body">
				<div class="table-responsive">
					<table class="table table-hover">
						<?php
                        $hasButtons = $this->hasButtons();
	    ?>
						<thead>
							<tr>
								<th class="center">#</th>
								<th><?php echo t('ticket.title'); ?></th>
								<th><?php echo t('ticketing.departments.status'); ?></th>
								<?php if ($hasButtons) { ?><th></th><?php } ?>
							</tr>
						</thead>
						<tbody>
							<?php
	        foreach ($this->getDepartments() as $department) {
	            $this->setButtonParam('edit', 'link', userpanel\url('settings/departments/edit/'.$department->id));
	            $this->setButtonParam('delete', 'link', userpanel\url('settings/departments/delete/'.$department->id));
	            ?>
							<tr>
								<td class="center"><?php echo $department->id; ?></td>
								<td><?php echo $department->title; ?></td>
								<td><?php echo $this->getDepartmentStatusLabel($department); ?></td>
								<?php
	                if ($hasButtons) {
	                    echo '<td class="center">'.$this->genButtons().'</td>';
	                }
	            ?>
							</tr>
							<?php
	        }
	    ?>
						</tbody>
					</table>
				</div>
				<?php $this->paginator(); ?>
			</div>
		</div>
	<?php } ?>
	</div>
</div>

<div class="modal fade" id="search" tabindex="-1" data-show="true" role="dialog">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h4 class="modal-title"><?php echo t('search'); ?></h4>
	</div>
	<div class="modal-body">
		<form id="departmentsearch" class="form-horizontal" action="<?php echo userpanel\url('settings/departments'); ?>" method="GET">
			<?php
            $this->setHorizontalForm('sm-3', 'sm-9');
$searchStatuses = $this->getDepartmentStatusForSelect();
array_unshift($searchStatuses, [
    'title' => '',
    'value' => '',
]);
$feilds = [
    [
        'name' => 'id',
        'type' => 'number',
        'label' => t('ticket.id'),
    ],
    [
        'name' => 'title',
        'label' => t('department.title'),
    ],
    [
        'name' => 'status',
        'type' => 'select',
        'label' => t('ticketing.departments.status'),
        'options' => $searchStatuses,
    ],
    [
        'type' => 'select',
        'label' => t('search.comparison'),
        'name' => 'comparison',
        'options' => $this->getComparisonsForSelect(),
    ],
];
foreach ($feilds as $input) {
    $this->createField($input);
}
?>
		</form>
	</div>
	<div class="modal-footer">
		<button type="submit" form="departmentsearch" class="btn btn-success"><?php echo t('search'); ?></button>
		<button type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true"><?php echo t('cancel'); ?></button>
	</div>
</div>
<?php
$this->the_footer();
