<?php
use packages\base\Translator;
use packages\userpanel;

$this->the_header();
?>
<div class="row">
	<div class="col-md-12">
		<!-- start: BASIC DELETE MESSAGE -->
		<form action="<?php echo userpanel\url('ticketing/delete/message/'.$this->getMessageData()->id); ?>" method="POST" role="form" id="delete_form" class="form-horizontal">
			<div class="alert alert-block alert-warning fade in">
				<h4 class="alert-heading"><i class="fa fa-exclamation-triangle"></i> <?php echo Translator::trans('message.delete.warning.title'); ?>!</h4>
				<p>
					<?php echo Translator::trans('message.delete.warning', ['message.id' => $this->getMessageData()->id]); ?>
				</p>
				<p>
					<a href="<?php echo userpanel\url('ticketing/view/'.$this->getMessageData()->ticket->id); ?>" class="btn btn-light-grey"><i class="fa fa-chevron-circle-<?php echo (bool) Translator::getLang()->isRTL() ? 'right' : 'left'; ?>"></i> <?php echo Translator::trans('return'); ?></a>
					<button type="submit" class="btn btn-danger"><i class="fa fa-trash-o"></i> <?php echo Translator::trans('ticketing.delete'); ?></button>
				</p>
			</div>
		</form>
		<!-- end: BASIC DELETE MESSAGE  -->
	</div>
</div>
<?php
$this->the_footer();
