<?php
use packages\base\Translator;
use packages\userpanel;

$this->the_header();
?>
<div class="row">
	<div class="col-md-12">
		<!-- start: BASIC LOCK TICKET -->
		<form action="<?php echo userpanel\url('ticketing/lock/'.$this->getTicketData()->id); ?>" method="POST" role="form" class="form-horizontal">
			<div class="alert alert-block alert-warning fade in">
				<h4 class="alert-heading"><i class="fa fa-exclamation-triangle"></i> <?php echo Translator::trans('ticket.lock.warning.title'); ?>!</h4>
				<p>
					<?php echo Translator::trans('ticket.lock.warning', ['ticket.id' => $this->getTicketData()->id]); ?>
				</p>
				<p>
					<a href="<?php echo userpanel\url('ticketing/view/'.$this->getTicketData()->id); ?>" class="btn btn-light-grey"><i class="fa fa-chevron-circle-<?php echo (bool) Translator::getLang()->isRTL() ? 'right' : 'left'; ?>"></i> <?php echo Translator::trans('return'); ?></a>
					<button type="submit" class="btn btn-yellow"><i class="fa fa-ban tip"></i> <?php echo Translator::trans('ticketing.lock'); ?></button>
				</p>
			</div>
		</form>
		<!-- end: BASIC LOCK TICKET  -->
	</div>
</div>
<?php
$this->the_footer();
