<?php
use \packages\base\translator;
use \packages\userpanel;
use \packages\userpanel\date;
use \themes\clipone\utility;
use \packages\ticketing\ticket;
$this->the_header();
?>
<div class="row">
	<div class="col-xs-12">
	<?php if(!empty($this->getTickets())){ ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="clip-user-6"></i> <?php echo translator::trans('tickets'); ?>
				<div class="panel-tools">
					<?php if($this->canAdd){ ?>
					<a class="btn btn-xs btn-link tooltips" title="<?php echo translator::trans('ticketing.add'); ?>" href="<?php echo userpanel\url('ticketing/new'); ?>"><i class="fa fa-plus"></i></a>
					<?php } ?>
					<a class="btn btn-xs btn-link tooltips" title="<?php echo translator::trans('search'); ?>" href="#search" data-toggle="modal" data-original-title=""><i class="fa fa-search"></i></a>
					<a class="btn btn-xs btn-link panel-collapse collapses" href="#"></a>
				</div>
			</div>
			<div class="panel-body">
				<div class="table-responsive">
					<table class="table table-hover">
						<?php
						$hasButtons = $this->hasButtons();
						?>
						<thead>
							<tr>
								<th class="center">#</th>
								<th><?php echo translator::trans('ticket.title'); ?></th>
								<?php if($this->multiuser){ ?><th><?php echo translator::trans('ticket.client'); ?></th><?php } ?>
								<th class="hidden-xs"><?php echo translator::trans('ticket.department'); ?></th>
								<th class="hidden-xs"><?php echo translator::trans('ticket.create_at'); ?></th>
								<th class="hidden-xs"><?php echo translator::trans('ticket.reply_at'); ?></th>
								<th><?php echo translator::trans('ticket.priority'); ?></th>
								<th><?php echo translator::trans('ticket.status'); ?></th>
								<?php if($hasButtons){ ?><th></th><?php } ?>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach($this->getTickets() as $row){
								$messageread = true;
								foreach($row->message as $status){
									if($status->status == 0){
										$messageread = false;
										break;
									}
								}

								$this->setButtonParam('view', 'link', userpanel\url("ticketing/view/".$row->id));
								$this->setButtonParam('delete', 'link', userpanel\url("ticketing/delete/".$row->id));
								$statusClass = utility::switchcase($row->status, [
									'label label-primary' => ticket::unread,
									'label label-info' => ticket::read,
									'label label-success' => ticket::answered,
									'label label-warning' => ticket::in_progress,
									'label label-inverse' => ticket::closed
								]);
								$statusTxt = utility::switchcase($row->status, [
									'unread' => ticket::unread,
									'read' => ticket::read,
									'answered' => ticket::answered,
									'in_progress' => ticket::in_progress,
									'closed' => ticket::closed
								]);
								$priorityClass = utility::switchcase($row->priority, [
									'label label-warning' => ticket::instantaneous,
									'label label-primary' => ticket::important,
									'label label-info' => ticket::ordinary
								]);
								$priorityTxt = utility::switchcase($row->priority, [
									'instantaneous' => ticket::instantaneous,
									'important' => ticket::important,
									'ordinary' => ticket::ordinary
								]);

								$title = ($messageread ? $row->title : "<b>".$row->title."</b>");
							?>
							<tr>
								<td class="center"><?php echo $row->id; ?></td>
								<td><?php echo $title; ?></td>
								<?php if($this->multiuser){ ?><td><a href="<?php echo userpanel\url('users/view/'.$row->client->id); ?>"><?php echo($row->client->getFullName()); ?></a></td><?php } ?>
								<td class="hidden-xs"><?php echo $row->department->title; ?></td>
								<td class="hidden-xs"><?php echo date::format('Y/m/d H:i', $row->create_at); ?></td>
								<td class="hidden-xs"><?php echo date::format('Y/m/d H:i', $row->reply_at); ?></td>
								<td><span class="<?php echo $priorityClass; ?>"><?php echo translator::trans($priorityTxt); ?></span></td>
								<td><span class="<?php echo $statusClass; ?>"><?php echo translator::trans($statusTxt); ?></span></td>
								<?php
								if($hasButtons){
									echo("<td class=\"center\">".$this->genButtons()."</td>");
								}
								?>
							</tr>
							<?php
							}
							?>
						</tbody>
					</table>
				</div>
				<?php $this->paginator(); ?>
			</div>
		</div>
		<div class="modal fade" id="search" tabindex="-1" data-show="true" role="dialog">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title"><?php echo translator::trans('search'); ?></h4>
			</div>
			<div class="modal-body">
				<form id="ticketSearch" class="form-horizontal" action="<?php echo userpanel\url("ticketing"); ?>" method="get">
					<?php
					$this->setHorizontalForm('sm-3','sm-9');
					$feilds = [
						[
							'name' => 'id',
							'type' => 'number',
							'label' => translator::trans("ticket.id")
						],
						[
							'name' => 'title',
							'label' => translator::trans("ticket.title")
						],
						[
							'name' => 'status',
							'type' => 'select',
							'label' => translator::trans("ticket.status"),
							'options' => $this->getStatusForSelect()
						],
						[
							'name' => 'priority',
							'type' => 'select',
							'label' => translator::trans("ticket.priority"),
							'options' => $this->getPriortyForSelect()
						],
						[
							'name' => 'department',
							'type' => 'select',
							'label' => translator::trans("ticket.department"),
							'options' => $this->getDepartmentsForSelect()
						],
						[
							'name' => 'word',
							'label' => translator::trans("ticketing.ticket.keyword")
						],
						[
							'type' => 'select',
							'label' => translator::trans('search.comparison'),
							'name' => 'comparison',
							'options' => $this->getComparisonsForSelect()
						]
					];
					if($this->multiuser){
						$userFields = [
							[
								'type' => 'hidden',
								'name' => 'client'
							],
							[
								'name' => 'client_name',
								'label' => translator::trans("ticket.client")
							]
						];
						array_splice($feilds, 2, 0, $userFields);
					}
					foreach($feilds as $input){
						$this->createField($input);
					}
					?>
				</form>
			</div>
			<div class="modal-footer">
				<button type="submit" form="ticketSearch" class="btn btn-success"><?php echo translator::trans("search"); ?></button>
				<button type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true"><?php echo translator::trans('cancel'); ?></button>
			</div>
		</div>
	<?php } ?>
	</div>
</div>
<?php
$this->the_footer();