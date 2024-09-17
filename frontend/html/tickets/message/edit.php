<?php
use packages\base\Translator;
use packages\userpanel;

$this->the_header();
?>

<div class="panel panel-default">
		<div class="panel-heading">
			<i class="clip-user-6"></i>
			<span><?php echo t('message.edit.notice.title').' #'.$this->message->id; ?></span>
		</div>
		<div class="panel-body">
			<form action="<?php echo userpanel\url('ticketing/edit/message/'.$this->message->id); ?>" method="POST" role="form" id="edit_form">
			<?php $this->loadContentEditor(); ?>
				<div class="row">
					<div class="col-sm-3">
						<a href="<?php echo userpanel\url('ticketing/view/'.$this->ticket->id); ?>" class="btn btn-light-grey"><i class="fa fa-chevron-circle-<?php echo Translator::isRTL() ? 'right' : 'left'; ?>"></i> <?php echo t('ticket.return'); ?></a>
						<button type="submit" class="btn btn-teal"><i class="fa fa-check-square-o"></i> <?php echo t('ticket.update'); ?></button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<?php
$this->the_footer();
