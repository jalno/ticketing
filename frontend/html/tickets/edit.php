<?php
use \packages\base;
use \packages\base\translator;
use \packages\userpanel;

$this->the_header();
?>
<div class="row ticket_edit">
    <div class="col-xs-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="clip-user-6"></i>
                <span><?php echo translator::trans("ticket.edit.notice.title").' #'.$this->ticket->id; ?></span>
            </div>
            <div class="panel-body">
				<form class="create_form" action="<?php echo userpanel\url('ticketing/edit/'.$this->ticket->id) ?>" method="post">
					<div class="col-sm-6">
						<?php
					$fields = array(
						array(
							'name' => 'title',
							'label' => translator::trans("ticket.title"),
						),
						array(
							'name' => 'priority',
							'type' => 'select',
							'label' => translator::trans("ticket.priority"),
							'options' => $this->getpriortyForSelect(),
						),
						array(
							'name' => 'department',
							'type' => 'select',
							'label' => translator::trans("ticket.department"),
							'options' => $this->getDepartmentForSelect(),
							)
						);
						foreach($fields as $field){
							$this->createField($field);
						}
						?>
					</div>
					<div class="col-sm-6">
						<?php
						$fields = array(
							array(
								'name' => 'client',
								'type' => 'hidden'
							),
							array(
								'name' => 'client_name',
								'label' => translator::trans("ticket.client"),
								'error' => array(
									'data_validation' => 'ticket.client.data_validation'
								)
							),
							array(
								'name' => 'status',
								'type' => 'select',
								'label' => translator::trans("ticket.status"),
								'options' => $this->getStatusForSelect()
							)
						);
						foreach($fields as $field){
							$this->createField($field);
						}
						?>
					</div>
					<div class="col-sm-12">
						<hr>
						<p>
							<a href="<?php echo userpanel\url('ticketing/view/'.$this->ticket->id); ?>" class="btn btn-light-grey"><i class="fa fa-chevron-circle-right"></i> <?php echo translator::trans('return'); ?></a>
							<button type="submit" class="btn btn-teal"><i class="fa fa-check-square-o"></i> <?php echo translator::trans("update") ?></button>
						</p>
					</div>
				</form>
            </div>
        </div>
    </div>
</div>
<?php
	$this->the_footer();
