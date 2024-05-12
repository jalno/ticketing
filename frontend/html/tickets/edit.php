<?php
use packages\base\Translator;
use packages\userpanel;

$this->the_header();
?>
<div class="row ticket_edit">
    <div class="col-xs-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="clip-user-6"></i>
                <span><?php echo Translator::trans('ticket.edit.notice.title').' #'.$this->ticket->id; ?></span>
            </div>
            <div class="panel-body">
				<form class="create_form" action="<?php echo userpanel\url('ticketing/edit/'.$this->ticket->id); ?>" method="post">
					<div class="col-sm-6">
						<?php
                    $fields = [
                        [
                            'name' => 'title',
                            'label' => Translator::trans('ticket.title'),
                        ],
                        [
                            'name' => 'priority',
                            'type' => 'select',
                            'label' => Translator::trans('ticket.priority'),
                            'options' => $this->getpriortyForSelect(),
                        ],
                        [
                            'name' => 'department',
                            'type' => 'select',
                            'label' => Translator::trans('ticket.department'),
                            'options' => $this->getDepartmentForSelect(),
                        ],
                    ];
foreach ($fields as $field) {
    $this->createField($field);
}
?>
					</div>
					<div class="col-sm-6">
						<?php
$fields = [
    [
        'name' => 'client',
        'type' => 'hidden',
    ],
    [
        'name' => 'client_name',
        'label' => Translator::trans('ticket.client'),
        'error' => [
            'data_validation' => 'ticket.client.data_validation',
        ],
    ],
    [
        'name' => 'status',
        'type' => 'select',
        'label' => Translator::trans('ticket.status'),
        'options' => $this->getStatusForSelect(),
    ],
];
foreach ($fields as $field) {
    $this->createField($field);
}
?>
					</div>
					<div class="col-sm-12">
						<hr>
						<p>
							<a href="<?php echo userpanel\url('ticketing/view/'.$this->ticket->id); ?>" class="btn btn-light-grey"><i class="fa fa-chevron-circle-right"></i> <?php echo Translator::trans('return'); ?></a>
							<button type="submit" class="btn btn-teal"><i class="fa fa-check-square-o"></i> <?php echo Translator::trans('update'); ?></button>
						</p>
					</div>
				</form>
            </div>
        </div>
    </div>
</div>
<?php
    $this->the_footer();
