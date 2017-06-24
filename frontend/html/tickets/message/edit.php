<?php
use \packages\base;
use \packages\base\frontend\theme;
use \packages\base\translator;
use \packages\base\http;

use \packages\userpanel;
use \packages\userpanel\user;
use \packages\userpanel\date;

use \themes\clipone\utility;

use \packages\ticketing\ticket;

use \packages\ticketing\Parsedown;

$this->the_header();
?>
<div class="row">
	<div class="col-md-12">
		<!-- start: BASIC DELETE MESSAGE -->
		<form action="<?php echo userpanel\url('ticketing/edit/message/'.$this->getMessageData()->id); ?>" method="POST" role="form" id="delete_form" class="form-horizontal">
			<div class="alert alert-block alert-info fade in">
				<h4 class="alert-heading"><i class="fa fa-edit tip tooltips"></i> <?php echo translator::trans('message.edit.notice.title'); ?>!</h4><br>
				<textarea name="text" rows="6" class="autosize form-control text-send"><?php echo $this->getMessageData()->text; ?></textarea>
				<hr>
				<p>
					<a href="<?php echo userpanel\url('ticketing/view/'.$this->getMessageData()->ticket); ?>" class="btn btn-light-grey"><i class="fa fa-chevron-circle-right"></i> <?php echo translator::trans('return'); ?></a>
					<button type="submit" class="btn btn-yellow"><i class="fa fa-check-square-o"></i> <?php echo translator::trans("update") ?></button>
				</p>
			</div>
		</form>
		<!-- end: BASIC DELETE MESSAGE  -->
	</div>
</div>
<?php
$this->the_footer();
