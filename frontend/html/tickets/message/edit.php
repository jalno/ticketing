<?php
use \packages\base\translator;
use \packages\userpanel;
$this->the_header();
?>
<div class="row">
	<div class="col-sm-12">
		<div class="panel panel-default">
				<div class="panel-heading">
					<i class="clip-user-6"></i>
					<span><?php echo translator::trans("message.edit.notice.title").' #'.$this->message->id; ?></span>
				</div>
				<div class="panel-body">
					<form action="<?php echo userpanel\url('ticketing/edit/message/'.$this->message->id); ?>" method="POST" role="form" id="delete_form" class="form-horizontal">
						<div class="col-sm-12">
							<?php
							$this->createField([
								'type' => 'textarea',
								'name' => 'text',
								'rows' => 6
							]);
							?>
						</div>
						<div class="row">
							<div class="col-sm-3">
								<a href="<?php echo userpanel\url('ticketing/view/'.$this->ticket->id); ?>" class="btn btn-light-grey"><i class="fa fa-chevron-circle-right"></i> <?php echo translator::trans('ticket.return'); ?></a>
								<button type="submit" class="btn btn-teal"><i class="fa fa-check-square-o"></i> <?php echo translator::trans("ticket.update") ?></button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
$this->the_footer();
