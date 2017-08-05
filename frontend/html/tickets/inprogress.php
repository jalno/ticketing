<?php
use \packages\base\translator;
use \packages\userpanel;
$this->the_header();
?>
<div class="row">
	<div class="col-sm-12">
		<form action="<?php echo userpanel\url('ticketing/inprogress/' . $this->ticket->id); ?>" method="POST" role="form" class="form-horizontal">
			<div class="alert alert-block alert-warning fade in">
				<h4 class="alert-heading"><i class="fa fa-exclamation-triangle"></i> <?php echo translator::trans('attention'); ?>!</h4>
				<p>
					<?php echo translator::trans("ticket.inprogress.warning", array('ticket.id' => $this->ticket->id)); ?>
				</p>
				<p>
					<a href="<?php echo userpanel\url('ticketing'); ?>" class="btn btn-light-grey"><i class="fa fa-chevron-circle-right"></i> <?php echo translator::trans('ticket.return'); ?></a>
					<button type="submit" class="btn btn-info"><i class="fa fa-tasks"></i> <?php echo translator::trans("ticket.inprogress") ?></button>
				</p>
			</div>
		</form>
	</div>
</div>
<?php
$this->the_footer();
