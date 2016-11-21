<?php
use \packages\base;
use \packages\base\translator;

use \packages\userpanel;
use \packages\userpanel\user;
use \packages\userpanel\date;

use \themes\clipone\utility;

use \packages\ticketing\ticket;

$this->the_header();
?>
<div class="row">
	<div class="col-md-12">
		<!-- start: BASIC TABLE PANEL -->
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-university"></i><?php echo translator::trans("department_add"); ?>
				<div class="panel-tools">
					<a class="btn btn-xs btn-link panel-collapse collapses" href="#"></a>
				</div>
			</div>
			<div class="panel-body">
				<form id="departmentsearch" class="form-horizontal" action="<?php echo userpanel\url("settings/departments/add"); ?>" method="post">
					<?php
						$this->setHorizontalForm('sm-2','sm-5');
						$this->createField(
						array(
							'name' => 'title',
							'label' => translator::trans("department.title")
						));
					?>

					<hr>
					<p>
						<a href="<?php echo userpanel\url("settings/departments"); ?>"class="btn btn-light-grey"><i class="fa fa-chevron-circle-right"></i> <?php echo translator::trans('return'); ?></a>
						<button type="submit" class="btn btn-success"><i class="fa fa-check-square-o"></i> <?php echo translator::trans("add"); ?></button>
					</p>
				</form>

			</div>
		</div>
		<!-- end: BASIC TABLE PANEL -->
	</div>
</div>
<?php
$this->the_footer();