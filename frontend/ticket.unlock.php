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
		<!-- start: BASIC LOCK TICKET -->
		<form action="<?php echo userpanel\url('ticketing/unlock/'.$this->getTicketData()->id); ?>" method="POST" role="form" class="form-horizontal">
			<div class="alert alert-block alert-info fade in">
				<h4 class="alert-heading"><i class="fa fa-exclamation-triangle"></i> <?php echo translator::trans('ticket.unlock.notice.title'); ?>!</h4>
				<p>
					<?php echo translator::trans("ticket.unlock.notice", array('ticket.id' => $this->getTicketData()->id)); ?>
				</p>
				<p>
					<a href="<?php echo userpanel\url('ticketing/view/'.$this->getTicketData()->id); ?>" class="btn btn-light-grey"><i class="fa fa-chevron-circle-right"></i> <?php echo translator::trans('return'); ?></a>
					<button type="submit" class="btn btn-yellow"><i class="fa fa-unlock tip"></i> <?php echo translator::trans("ticketing.unlock") ?></button>
				</p>
			</div>
		</form>
		<!-- end: BASIC LOCK TICKET  -->
	</div>
</div>
<?php
$this->the_footer();