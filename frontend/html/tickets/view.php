<?php
use \packages\base\frontend\theme;
use \packages\base\translator;
use \packages\userpanel;
use \packages\userpanel\date;
use \packages\ticketing\ticket;
use \packages\ticketing\authorization;
$this->the_header();
$product = $this->getProductService();
$childrenType = (bool)authorization::childrenTypes();
?>
<div class="row">
<?php if($childrenType or $product){ ?>
	<div class="col-md-4 col-md-pull-8">
		<?php if($childrenType){ ?>
		<div class="row">
			<div class="col-sm-12">
				<div class="panel panel-default">
					<div class="panel-heading"><i class="fa fa-hdd-o"></i> <?php echo translator::trans('ticket.client'); ?>
						<div class="panel-tools">
							<?php $client = $this->ticket->client; ?>
							<a href="<?php echo userpanel\url('users/view/'.$client->id); ?>" target="_blank" class="btn btn-xs btn-link tooltips" title="<?php echo translator::trans('view'); ?>"><i class="fa fa-user"></i></a>
							<a href="#" class="btn btn-xs btn-link panel-collapse collapses"></a>
						</div>
					</div>
					<div class="panel-body form-horizontal">
						<div class="form-group">
							<label class="col-xs-5"><?php echo translator::trans('ticket.client.type'); ?>:</label>
							<div class="col-xs-7"><?php echo $client->type->title; ?></div>
						</div>
						<div class="form-group"><label class="col-xs-3"><?php echo translator::trans('ticket.client.email'); ?>:</label>
							<div class="col-xs-9 ltr">
								<a href="<?php echo userpanel\url('email/send/', ['user' => $client->id]); ?>"><?php echo $client->email; ?></a>
							</div>
						</div>
						<div class="form-group">
							<label class="col-xs-3"><?php echo translator::trans('ticket.client.phone'); ?>:</label>
							<div class="col-xs-9 ltr"><?php echo $client->phone; ?></div>
						</div>
						<div class="form-group">
							<label class="col-xs-5"><?php echo translator::trans('ticket.client.cellphone'); ?>:</label>
							<div class="col-xs-7 ltr">
								<a href="<?php echo userpanel\url('sms/send/', ['user' => $client->id]); ?>"><?php echo $client->cellphone; ?></a>
							</div>
						</div>
						<div class="form-group">
							<label class="col-xs-5"><?php echo translator::trans('ticket.client.lastonline'); ?>:</label>
							<div class="col-xs-7"><?php echo date::relativeTime($client->lastonline); ?></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		}
		if($product){
			echo $product->generateRows();
		}
		?>
	</div>
<?php } ?>
	<div class="<?php echo (($childrenType or $product) ? 'col-md-8 col-md-push-4' : 'col-sm-12'); ?>">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-comment-o"></i>
				<span><?php echo $this->ticket->title; ?></span>
				<div class="panel-tools">
					<?php if($this->canEdit){ ?>
						<a class="btn btn-xs btn-link tooltips" title="<?php echo translator::trans("ticket.setting"); ?>" href="<?php echo userpanel\url('ticketing/edit/'.$this->ticket->id); ?>"><i class="fa fa-wrench tip tooltips"></i></a>
						<?php if($this->canSend == true){ ?>
						<a class="btn btn-xs btn-link tooltips" title="<?php echo translator::trans("ticket.lock"); ?>" href="<?php echo userpanel\url('ticketing/lock/'.$this->ticket->id); ?>"><i class="fa fa-ban tip tooltips"></i></a>
						<?php }else{ ?>
						<a class="btn btn-xs btn-link tooltips"  title="<?php echo translator::trans("ticket.unlock"); ?>" href="<?php echo userpanel\url('ticketing/unlock/'.$this->ticket->id); ?>"><i class="fa fa-unlock tip tooltips"></i></a>
				  <?php }
					}
					if($this->canDel){ ?>
						<a class="btn btn-xs btn-link tooltips" title="<?php echo translator::trans("ticket.delete.warning.title"); ?>" href="<?php echo userpanel\url('ticketing/delete/'.$this->ticket->id); ?>"><i class="fa fa-trash-o tip"></i></a>
					<?php } ?>
					<?php if($this->ticket->status != ticket::closed and $this->canClose){ ?>
					<a id="ticket-close" data-ticket="<?php echo $this->ticket->id; ?>" class="btn btn-xs btn-link tooltips" title="<?php echo translator::trans("ticket.close"); ?>" href="<?php echo userpanel\url('ticketing/close/'.$this->ticket->id); ?>"><i class="fa fa-times" ></i></a>
					<?php } ?>
					<a class="btn btn-xs btn-link panel-collapse collapses" href="#"></a>

				</div>
			</div>
			<div  class="panel-body tecket-message">
				<div class="row">
					<div class="col-sm-12">
						<?php foreach($this->messages as $message){ ?>
						<div class="msgbox <?php echo ($message->user->id == $this->ticket->client->id) ? 'itemIn' : 'itemOut'; ?>" id="message-<?php echo $message->id; ?>">
							<a class="image" href="<?php echo userpanel\url('users/view/'.$message->user->id); ?>"><img src="<?php echo(theme::url('assets/images/user.png')) ?>" class="img-polaroid"></a>
							<div class="text">
								<div class="info clearfix">
									<span class="name">
										<a href="<?php echo userpanel\url('users/view/'.$message->user->id); ?>"><?php echo $message->user->getFullName(); ?></a>
									</span>
									<span class="date tooltips" title="<?php echo date::format('Y/m/d H:i:s', $message->date); ?>"><?php echo date::relativeTime($message->date); ?></span>
								</div>
								<div class="msgtext">
									<?php echo $message->content; ?>
									<?php if($message->files){?>
									<div class="message-files">
										<p><?php echo translator::trans("attachment.files"); ?></p>
										<ul>
											<?php foreach($message->files as $file){ ?>
												<li><a href="<?php echo userpanel\url('ticketing/download/'.$file->id); ?>" target="_blank"><?php echo $file->name; ?></a></li>
											<?php } ?>
										</ul>
									</div>
									<?php } ?>
								</div>
							</div>
							<div class="icons">
								<?php if($this->canEditMessage){ ?>
								<a class="msg-edit" href="<?php echo userpanel\url('ticketing/edit/message/'.$message->id); ?>"><i class="fa fa-edit tip tooltips" title="<?php echo translator::trans("message.edit.notice.title"); ?>"></i></a>
								<?php } if($this->canDelMessage){ ?>
								<a class="msg-del" href="<?php echo userpanel\url('ticketing/delete/message/'.$message->id); ?>"><i class="fa fa-times tip tooltips" title="<?php echo translator::trans("message.delete.warning.title"); ?>"></i></a>
								<?php } ?>
							</div>
						</div>
						<?php } ?>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<div class="replaycontianer">
							<h3 style="font-family: b;"><?php echo translator::trans('send.reply'); ?></h3>
							<form id="ticket-reply" action="<?php echo userpanel\url('ticketing/view/'.$this->ticket->id); ?>" method="post" enctype="multipart/form-data">
								<div class="row">
									<div class="col-sm-12">
										<textarea <?php if(!$this->canSend){echo("disabled");} ?> name="text" rows="4" class="autosize form-control text-send"></textarea>
										<hr>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-7">
										<p><?php echo translator::trans('markdown.description'); ?></p>
									</div>
									<div class="col-sm-5 text-center">
										<div class="row btn-group btn-group-lg" role="group">
											<span class="btn btn-file2">
												<i class="fa fa-upload"></i> <?php echo translator::trans("upload") ?>
												<input type="file" name="file[]" multiple="" <?php echo !$this->canSend ? 'disabled' : ''; ?>>
											</span>
											<button <?php if(!$this->canSend){echo("disabled");} ?> class="btn btn-teal" type="submit"><i class="fa fa-paper-plane"></i><?php echo translator::trans("send"); ?></button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
$this->the_footer();
