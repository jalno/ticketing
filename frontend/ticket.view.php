<?php
use \packages\base;
use \packages\base\frontend\theme;
use \packages\base\translator;
use \packages\base\http;

use \packages\userpanel;
use \packages\userpanel\user;
use \packages\userpanel\date;
use \packages\base\views\FormError;

use \themes\clipone\utility;

use \packages\ticketing\ticket;


$this->the_header();
?>
<div class="row">
	<div class="col-md-12">
		<!-- start: BASIC TABLE PANEL -->
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-comment-o"></i>
				<span><?php echo $this->getTicketData()->title; ?></span>
				<div class="panel-tools">
					<?php if($this->canEdit){ ?>
						<a class="btn btn-xs btn-link tooltips" title="<?php echo translator::trans("ticket.setting"); ?>" href="<?php echo userpanel\url('ticketing/edit/'.$this->getTicketData()->id); ?>"><i class="fa fa-wrench tip tooltips"></i></a>
						<a class="btn btn-xs btn-link tooltips" title="<?php echo translator::trans("ticket.close"); ?>"><i class="fa fa-times tip tooltips" ></i></a>
						<?php if($this->canSend == true){ ?>
						<a class="btn btn-xs btn-link tooltips" title="<?php echo translator::trans("ticket.lock"); ?>" href="<?php echo userpanel\url('ticketing/lock/'.$this->getTicketData()->id); ?>"><i class="fa fa-ban tip tooltips"></i></a>
						<?php }else{ ?>
						<a class="btn btn-xs btn-link tooltips"  title="<?php echo translator::trans("ticket.unlock"); ?>" href="<?php echo userpanel\url('ticketing/unlock/'.$this->getTicketData()->id); ?>"><i class="fa fa-unlock tip tooltips"></i></a>
				  <?php }
					} if($this->canDel){ ?>
						<a class="btn btn-xs btn-link tooltips" title="<?php echo translator::trans("ticket.delete.warning.title"); ?>" href="<?php echo userpanel\url('ticketing/delete/'.$this->getTicketData()->id); ?>"><i class="fa fa-trash-o tip"></i></a>
					<?php } ?>
					<a class="btn btn-xs btn-link panel-collapse collapses" href="#"></a>

				</div>
			</div>
			<div  class="panel-body">
				<?php foreach($this->messages as $message){ ?>
					<div class="space clearfix <?php if($message->user->id != $this->getTicketData()->client->id){echo("reply");} ?>" id="ticket_message-<?php echo $message->id; ?>">
					    <div class="comment-photo">
							<img class="img-polaroid" src="<?php echo(theme::url('assets/images/user.png')) ?>" height="60" width="60" alt="client">
						</div>
					    <div class="comment-wraper text">
					        <a href="<?php echo userpanel\url('users/view/'.$message->user->id); ?>"><h5 class="h-comment m-right-1"><?php echo $message->user->name." ".$message->user->lastname; ?></h5></a>
					        <div class="space black m-right-1">
					            <?php echo $message->content;
								if($message->files){?>
								<br><hr>
								<p class="smal">
									<?php echo translator::trans("attachment.files"); ?>
								</p>
								<p>
									<?php foreach($message->files as $file){ ?>
										<a href="<?php echo userpanel\url('ticketing/download/'.$file->id); ?>"><?php echo $file->name; ?></a>
									<?php } ?>
								</p>
								<?php } ?>
					        </div>
					        <div class="panel-heading">
								<i class="fa fa-clock-o"></i>

								<a class="tooltips cursor-default" title="<?php echo date::format('Y/m/d H:i:s', $message->date);?>"><span class="meta-tag"><?php echo $message->lastime; ?></span><a>
								<div class="panel-tools">
									<?php if($this->canEditMessage){ ?>
										<a class="btn btn-xs btn-link ticket-options" href="<?php echo userpanel\url('ticketing/edit/message/'.$message->id); ?>"><i class="fa fa-edit tip tooltips" title="<?php echo translator::trans("message.edit.notice.title"); ?>"></i></a>
									<?php } if($this->canDelMessage){ ?>
										<a class="btn btn-xs btn-link ticket-delete" href="<?php echo userpanel\url('ticketing/delete/message/'.$message->id); ?>"><i class="fa fa-trash-o tip tooltips" title="<?php echo translator::trans("message.delete.warning.title"); ?>"></i></a>
									<?php } ?>
								</div>
							</div>
					    </div>
					</div>
				<?php } ?>
				<div class="replaycontianer">
					<h3 style="font-family: b;"><?php echo translator::trans('send.reply'); ?></h3>
					<form action="<?php echo userpanel\url('ticketing/view/'.$this->getTicketData()->id); ?>" method="post" enctype="multipart/form-data">
						<div class="row">
							<div class="col-md-12">
								<textarea <?php if($this->canSend == false){echo("disabled");} ?> name="text" rows="4" class="autosize form-control text-send"></textarea>
								<hr>
							</div>
						</div>
						<div class="row">
							<div class="col-md-8">
								<p><?php echo translator::trans('markdown.description'); ?></p>
							</div>
							<div class="col-md-4">
								<div class="col-md-12 btn-group btn-group-lg" role="group">
									<span class="btn btn-file2 btn-default">
										<i class="fa fa-upload"></i> <?php echo translator::trans("upload") ?>
										<input type="file" name="file">
									</span>
									<button <?php if($this->canSend == false){echo("disabled");} ?> class="btn btn-teal btn-default" type="submit"><i class="fa fa-paper-plane"></i><?php echo translator::trans("send"); ?></button>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<!-- end: BASIC TABLE PANEL -->
	</div>
</div>
<?php
$this->the_footer();
