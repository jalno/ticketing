<?php
use \packages\base;
use \packages\base\frontend\theme;
use \packages\base\translator;
use \packages\base\http;

use \packages\userpanel;

use \themes\clipone\utility;

$this->the_header();
?>
<div class="row">
	<div class="col-md-12">
		<!-- start: Delete Department -->
		<form action="<?php echo userpanel\url('settings/departments/delete/'.$this->getDepartmentData()->id); ?>" method="POST" role="form" class="form-horizontal">
			<div class="alert alert-block alert-warning fade in">
				<h4 class="alert-heading"><i class="fa fa-exclamation-triangle"></i> <?php echo translator::trans('attention'); ?>!</h4>
				<p>
					<?php echo translator::trans("department.delete.warning", array('department_id' => $this->getDepartmentData()->id)); ?>
				</p>
				<p>
					<a href="<?php echo userpanel\url('settings/departments'); ?>" class="btn btn-light-grey"><i class="fa fa-chevron-circle-right"></i> <?php echo translator::trans('return'); ?></a>
					<button type="submit" class="btn btn-yellow"><i class="fa fa-trash-o tip"></i> <?php echo translator::trans("department.delete") ?></button>
				</p>
			</div>
		</form>
		<!-- end: Delete Department  -->
	</div>
</div>
<?php
$this->the_footer();
