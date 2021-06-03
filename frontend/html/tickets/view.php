<?php
use packages\base\translator;
use packages\userpanel;
use packages\userpanel\Date;
use packages\ticketing\{Authentication, Authorization, Ticket, Ticket_Message};
use themes\clipone\{Utility};

$product = $this->getProductService();
$childrenType = (bool)authorization::childrenTypes();
$isRTL = Translator::getLang()->isRTL();
$this->the_header();
?>
<h1 class="visible-print-block"><?php echo t("ticket") . " #" . $this->ticket->id; ?></h1>
<div class="row">
	<div class="col-md-8">
		<div class="panel panel-white panel-ticket-messages">
			<div  class="panel-body ticket-message">
				<?php
				foreach ($this->messages as $message) {
					$hasAccess = $this->hasAccessToUser($message->user);
				?>
				<div class="msgbox <?php echo ($message->user->id == $this->ticket->client->id) ? 'itemIn' : 'itemOut'; ?>" id="message-<?php echo $message->id; ?>">
					<?php if ($hasAccess) { ?>
					<a class="image" href="<?php echo userpanel\url('users/view/'.$message->user->id); ?>">
					<?php } ?>
						<img class="img-polaroid<?php if (!$hasAccess) echo " image"; ?>" src="<?php echo $this->getUserAvatar($message->user); ?>">
					<?php if ($hasAccess) { ?>
					</a>
					<?php } ?>
					<div class="text">
						<div class="info clearfix">
							<span class="name">
							<?php if ($this->hasAccessToUser($message->user)) { ?>
								<a href="<?php echo userpanel\url('users/view/'.$message->user->id); ?>"><?php echo $message->user->getFullName(); ?><span class="visible-print-inline-block">(#<?php echo $message->user->id; ?>)</span></a>
							<?php } else { ?>
								<?php echo $message->user->getFullName(); ?><span class="visible-print-inline-block">(#<?php echo $message->user->id; ?>)</span>
							<?php } ?>
							</span>
							<span class="date tooltips hidden-print" title="<?php echo Date::format('Y/m/d H:i:s', $message->date); ?>"><?php echo Date::relativeTime($message->date); ?></span>
							<span class="date visible-print-inline-block ltr"><?php echo Date::format('Y/m/d H:i:s', $message->date); ?></span>
						</div>
						<div class="msgtext">
							<?php echo $message->content; ?>
							<?php if ($message->files) {?>
							<div class="message-files">
								<div class="title">
									<span><?php echo t("attachment.files"); ?></span>
								</div>
								<div class="content py-4">
									<?php foreach ($message->files as $file) { ?>
										<div class="d-inline-block bg-light-gray py-2 px-3 rounded mt-2 my-4 ml-3">
											<a href="<?php echo userpanel\url("ticketing/download/{$file->id}"); ?>" target="_blank"><?php echo $file->name; ?></a>
											<span class="text-success mr-2 check-file-icon"><i class="fa fa-check fa-lg"></i></span>
										</div>
									<?php } ?>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>
					<div class="icons hidden-print">
						<?php
						if ($hasAccess) {
							if ($this->canEditMessage) {
						?>
						<a class="msg-edit" href="<?php echo userpanel\url('ticketing/edit/message/'.$message->id); ?>"><i class="fa fa-edit tip tooltips" title="<?php echo t("message.edit.notice.title"); ?>"></i></a>
						<?php
							}
							if ($this->canDelMessage) {
						?>
						<a class="msg-del" href="<?php echo userpanel\url('ticketing/delete/message/'.$message->id); ?>"><i class="fa fa-times tip tooltips" title="<?php echo t("message.delete.warning.title"); ?>"></i></a>
						<?php
							}
						}
						?>
					</div>
				</div>
				<?php } ?>
				<div class="row hidden-print">
					<div class="col-sm-12">
						<div class="replaycontianer">
							<h3><?php echo t('send.reply'); ?></h3>
							<form id="ticket-reply" action="<?php echo userpanel\url('ticketing/view/'.$this->ticket->id); ?>" method="post" enctype="multipart/form-data" spellcheck="false">
								<div class="ticket-text-wrapper form-group border p-3">
									<?php
									$fields = array(
										array(
											'name' => 'text',
											'type' => 'textarea',
											'rows' => 4,
											'required' => true,
											'disabled' => !$this->canSend,
											'class' => 'form-control autosize text-send border-0 no-resize rounded-0',
										),
									);
									if ($this->canEnableDisableNotification) {
										$fields[] = array(
											'name' => "send_notification",
											"type" => "hidden",
										);
									}
									foreach ($fields as $field) {
										$this->createField($field);
									}
									?>
									<div class="attachments mt-3">
										<div class="title">
											<span><?php echo t("attachment.files"); ?></span>
										</div>
										<div class="content py-4" id="attachmentsContent"></div>
										<div id="progressBar">
											<div class="progress d-inline-block">
												<div class="progress-bar progress-bar-fill bg-blue-gray"></div>
											</div>
											<span class="progress-bar-text mr-2 ">0%</span>
										</div>
									</div>
								</div>
								<hr>
								<div class="row">
									<?php
									$editor = authentication::getUser()->getOption('ticketing_editor');
									if(!$editor or $editor == ticket_message::html){
									?>
									<div class="col-sm-7">
										<p><?php echo translator::trans('markdown.description', ['settings.url'=>userpanel\url('profile/settings')]); ?></p>
									</div>
									<?php } ?>
									<div class="col-sm-5 text-center <?php echo $editor != ticket_message::html ? 'col-sm-offset-7' : ''; ?>">
										<div class="row btn-group btn-group-lg" role="group">
										<?php if ($this->canEnableDisableNotification) { ?>
											<div class="btn-group btn-group-lg btn-group-notification-behavior" role="group">
												<button type="button" class="btn btn-teal dropdown-toggle btn-select-notification-behavior" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
													<i class="fa fa-caret-down" aria-hidden="true"></i>
												</button>
												<button type="submit" class="btn btn-teal btn-send">
													<div class="btn-icons"><i class="fa fa-<?php echo ($this->sendNotification ? "bell" : "bell-slash") ?>" aria-hidden="true"></i></div>
												<?php echo t("send"); ?>
												</button>
												<ul class="dropdown-menu select-notification-behavior">
													<li>
														<a class="notification-behavior with-notification">
															<div class="btn-icons"><i class="fa fa-bell" aria-hidden="true"></i></div>
														<?php echo t("ticketing.send.with_notification"); ?>
														</a>
													</li>
													<li>
														<a class="notification-behavior without-notification">
															<div class="btn-icons"><i class="fa fa-bell-slash-o" aria-hidden="true"></i></div>
														<?php echo t("ticketing.send.without_notification"); ?>
														</a>
													</li>
												</ul>
											</div>
										<?php } else { ?>
											<button <?php echo (!$this->canSend ? 'disabled' : ""); ?> class="btn btn-teal" type="submit">
												<div class="btn-icons"><i class="fa fa-paper-plane"></i></div>
											<?php echo t("send"); ?>
											</button>
										<?php } ?>
											<span class="btn btn-file2 <?php echo !$this->canSend ? 'disabled' : ''; ?>">
												<div class="btn-icons"><i class="fa fa-upload"></i></div>
											<?php echo translator::trans("upload") ?>
												<input type="file" id="uploadFiles" name="_file" multiple="" <?php echo !$this->canSend ? 'disabled' : ''; ?>>
											</span>
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


	<div class="col-md-4">
		<div class="panel panel-white panel-ticket-info">
			<div class="panel-heading"><i class="fa fa-ticket fa-rotate-45"></i> <?php echo t('ticket'); ?>
				<div class="panel-tools hidden-print">
					<?php $client = $this->ticket->client; ?>
					<?php if ($this->canEdit) { ?>
						<a id="ticket-edit" class="btn btn-xs btn-link tooltips" title="<?php echo translator::trans("ticket.setting"); ?>" href="<?php echo userpanel\url('ticketing/edit/'.$this->ticket->id); ?>"><i class="fa fa-cog"></i></a>
						<?php if($this->ticket->status != ticket::in_progress){ ?>
							<a id="ticket-inProgress" data-ticket="<?php echo $this->ticket->id; ?>" class="btn btn-xs btn-link tooltips" title="<?php echo translator::trans("in_progress"); ?>" href="<?php echo userpanel\url('ticketing/inprogress/'.$this->ticket->id); ?>"><i class="fa fa-tasks" ></i></a>
						<?php } ?>

						<?php if (!$this->isLocked) { ?>
							<a class="btn btn-xs btn-link tooltips" title="<?php echo translator::trans("ticket.lock"); ?>" href="<?php echo userpanel\url('ticketing/lock/'.$this->ticket->id); ?>"><i class="fa fa-ban tip tooltips"></i></a>
						<?php } else { ?>
							<a class="btn btn-xs btn-link tooltips"  title="<?php echo translator::trans("ticket.unlock"); ?>" href="<?php echo userpanel\url('ticketing/unlock/'.$this->ticket->id); ?>"><i class="fa fa-unlock tip tooltips"></i></a>
						<?php } ?>
					<?php } ?>

					<?php if ($this->canDel) { ?>
						<a class="btn btn-xs btn-link tooltips" title="<?php echo translator::trans("ticket.delete.warning.title"); ?>" href="<?php echo userpanel\url('ticketing/delete/'.$this->ticket->id); ?>"><i class="fa fa-trash-o tip"></i></a>
					<?php } ?>

					<?php if ($this->ticket->status != ticket::closed and $this->canClose) { ?>
						<a id="ticket-close" data-ticket="<?php echo $this->ticket->id; ?>" class="btn btn-xs btn-link tooltips" title="<?php echo translator::trans("ticket.close"); ?>" href="<?php echo userpanel\url('ticketing/close/'.$this->ticket->id); ?>"><i class="fa fa-times" ></i></a>
					<?php } ?>
				</div>
			</div>
			<div class="panel-body form-horizontal">
				<div class="form-group">
					<label class="col-xs-3"><?php echo t('ticket.department'); ?></label>
					<div class="col-xs-9 text-<?php echo $isRTL ? "left" : "right"; ?>"><?php echo $this->ticket->department->title; ?></div>
				</div>
				<div class="form-group"><label class="col-xs-3"><?php echo t('ticket.priority'); ?></label>
					<div class="col-xs-9 text-<?php echo $isRTL ? "left" : "right"; ?>">
					<?php
						$priorityClass = Utility::switchcase($this->ticket->priority, array(
							"label-warning" => Ticket::instantaneous,
							"label-primary" => Ticket::important,
							"label-info" => Ticket::ordinary,
						));
						$priorityText = Utility::switchcase($this->ticket->priority, array(
							"instantaneous" => Ticket::instantaneous,
							"important" => Ticket::important,
							"ordinary" => Ticket::ordinary,
						));
					?>
						<span class="label <?php echo $priorityClass; ?> label-border ticket-priority"><?php echo t($priorityText); ?></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-3"><?php echo t('ticket.status'); ?></label>
					<div class="col-xs-9 text-<?php echo $isRTL ? "left" : "right"; ?>">
					<?php
						$statusClass = Utility::switchcase($this->ticket->status, array(
							"label-primary" => Ticket::unread,
							"label-info" => Ticket::read,
							"label-success" => Ticket::answered,
							"label-warning" => Ticket::in_progress,
							"label-inverse" => Ticket::closed
						));
						$statusText = Utility::switchcase($this->ticket->status, array(
							"unread" => Ticket::unread,
							"read" => Ticket::read,
							"answered" => Ticket::answered,
							"in_progress" => Ticket::in_progress,
							"closed" => Ticket::closed
						));
					?>
						<span class="label <?php echo $statusClass; ?> label-border ticket-status"><?php echo t($statusText); ?></span>
					</div>
				</div>

				<?php if ($this->canEdit) { ?>
					<div class="operator-input-container">
						<form class="hidden-print" id="set-operator-form" data-department="<?php echo $this->ticket->department->id; ?>" action="<?php echo userpanel\url("ticketing/edit/{$this->ticket->id}"); ?>" method="POST">
					<?php
						$this->createField(array(
							"name" => "operator",
							"type" => "hidden",
						));
						$this->createField(array(
							"name" => "operator_name",
							"label" => t("ticketing.ticket.operator"),
							"input-group" => array(
								"right" => array(
									array(
										"type" => "submit",
										"class" => "btn btn-success",
										"text" => t("submit"),
									),
								),
							),
						));
					?>
						</form>
					</div>
				<?php } ?>

				<?php if ($childrenType) { ?>
					<div class="client-container">
						<div class="ticket-info-panel-heading">
							<div class="client-info">
								<img src="<?php echo $this->getUserAvatar($this->ticket->client); ?>" class="circle-img" alt="" width="30" height="30">
								<span class="client-full-name"><?php echo $this->ticket->client->getFullName(); ?></span>
							</div>
							<?php if (Authorization::is_accessed("users_view", "userpanel")) { ?>
							<div class="ticket-info-panel-tools">
								<a href="<?php echo userpanel\url('users/view/'.$client->id); ?>" target="_blank" class="btn btn-xs btn-link tooltips" title="<?php echo t("user.view_profile"); ?>"><i class="fa fa-user"></i></a>
							</div>
							<?php } ?>
						</div>
						<div class="form-group">
							<label class="col-xs-5"><i class="fa fa-tag" aria-hidden="true"></i> <?php echo t('ticket.client.type'); ?></label>
							<div class="col-xs-7 text-<?php echo $isRTL ? "left" : "right"; ?>"><span class="label label-border label-secondary"><?php echo $client->type->title; ?></span></div>
						</div>
						<div class="form-group">
							<label class="col-xs-4"><i class="fa fa-envelope-o" aria-hidden="true"></i> <?php echo t('ticket.client.email'); ?></label>
							<div class="col-xs-8 text-<?php echo $isRTL ? "left" : "right"; ?>">
								<a href="<?php echo userpanel\url('email/send/', ['user' => $client->id]); ?>"><?php echo $client->email; ?></a>
							</div>
						</div>
						<div class="form-group">
							<label class="col-xs-5"><i class="fa fa-phone fa-flip-horizontal" aria-hidden="true"></i> <?php echo t('ticket.client.cellphone'); ?></label>
							<div class="col-xs-7 text-<?php echo $isRTL ? "left" : "right"; ?> client-cellphone">
								<a href="<?php echo userpanel\url('sms/send/', ['user' => $client->id]); ?>"><?php echo $client->getCellphoneWithDialingCode(); ?></a>
							</div>
						</div>
					</div>
				<?php } ?>

				<?php if ($product) { ?>
					<div class="product-container">
					<?php echo $product->generateRows(); ?>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>

<?php if($this->canEdit){ ?>
<div class="modal fade" id="settings" tabindex="-1" data-show="true" role="dialog">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h4 class="modal-title"><?php echo translator::trans('ticket.edit.notice.title'); ?></h4>
	</div>
	<div class="modal-body ticket_edit">
		<form  data-department="<?php echo $this->ticket->department->id; ?>" id="editForm" class="form-horizontal create_form" action="<?php echo userpanel\url("ticketing/edit/".$this->ticket->id); ?>" method="POST">
			<?php
			$this->setHorizontalForm('sm-3','sm-9');
			$feilds = [
				[
					'name' => 'title',
					'label' => translator::trans("ticket.title")
				],
				[
					'type' => 'hidden',
					'name' => 'client'
				],
				[
					'name' => 'client_name',
					'label' => translator::trans("ticket.client")
				],
				[
					'name' => 'priority',
					'type' => 'select',
					'label' => translator::trans("ticket.priority"),
					'options' => $this->getPriortyForSelect()
				],
				[
					'name' => 'status',
					'type' => 'select',
					'label' => translator::trans("ticket.status"),
					'options' => $this->getStatusForSelect()
				],
				[
					'name' => 'department',
					'type' => 'select',
					'label' => translator::trans("ticket.department"),
					'options' => $this->getDepartmentForSelect()
				],
				array(
					"name" => "operator",
					"type" => "hidden",
				),
				array(
					"name" => "operator_name",
					"label" => t("ticketing.ticket.operator"),
				),
			];
			foreach($feilds as $input){
				$this->createField($input);
			}
			?>
		</form>
	</div>
	<div class="modal-footer">
		<button type="submit" form="editForm" class="btn btn-success"><?php echo translator::trans("update"); ?></button>
		<button type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true"><?php echo translator::trans('cancel'); ?></button>
	</div>
</div>
<?php
}
$this->the_footer();
