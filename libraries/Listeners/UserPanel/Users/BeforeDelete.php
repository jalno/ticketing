<?php

namespace packages\ticketing\Listeners\UserPanel\Users;

use packages\base\{View\Error};
use packages\ticketing\Authorization;
use packages\ticketing\Ticket;
use packages\ticketing\TicketMessage;
use packages\userpanel\Events as UserpanelEvents;

use function packages\userpanel\url;

class BeforeDelete
{
    public function check(UserpanelEvents\Users\BeforeDelete $event): void
    {
        $this->checkTicketsClient($event);
        $this->checkTicketsOperator($event);
        $this->checkTicketsMessages($event);
    }

    private function checkTicketsClient(UserpanelEvents\Users\BeforeDelete $event): void
    {
        $user = $event->getUser();
        $hasTickets = (new Ticket())->where('client', $user->id)->has();
        if (!$hasTickets) {
            return;
        }
        $message = t('error.packages.ticketing.error.tickets.client.delete_user_warn.message');
        $error = new Error('packages.ticketing.error.tickets.client.delete_user_warn');
        $error->setType(Error::WARNING);
        if (Authorization::is_accessed('list')) {
            $message .= '<br> '.t('packages.ticketing.error.tickets.client.delete_user_warn.view_tickets').' ';
            $error->setData([
                [
                    'txt' => '<i class="fa fa-search"></i> '.t('packages.ticketing.error.tickets.client.delete_user_warn.view_tickets_btn'),
                    'type' => 'btn-warning',
                    'link' => url('ticketing', [
                        'client' => $user->id,
                        'status' => implode(',', Ticket::STATUSES),
                    ]),
                ],
            ], 'btns');
        } else {
            $message .= '<br> '.t('packages.ticketing.error.tickets.client.delete_user_warn.view_tickets.tell_someone');
        }
        $error->setMessage($message);

        $event->addError($error);
    }

    private function checkTicketsOperator(UserpanelEvents\Users\BeforeDelete $event): void
    {
        $user = $event->getUser();
        $hasTickets = (new Ticket())->where('operator_id', $user->id)->has();
        if (!$hasTickets) {
            return;
        }
        $message = t('error.packages.ticketing.error.tickets.operator.delete_user_warn.message');
        $error = new Error('packages.ticketing.error.tickets.operator.delete_user_warn');
        $error->setType(Error::WARNING);
        if (Authorization::is_accessed('list')) {
            $message .= '<br> '.t('packages.ticketing.error.tickets.operator.delete_user_warn.view_tickets').' ';
            $error->setData([
                [
                    'txt' => '<i class="fa fa-search"></i> '.t('packages.ticketing.error.tickets.operator.delete_user_warn.view_tickets_btn'),
                    'type' => 'btn-warning',
                    'link' => url('ticketing', [
                        'operator' => $user->id,
                        'status' => implode(',', Ticket::STATUSES),
                    ]),
                ],
            ], 'btns');
        } else {
            $message .= '<br> '.t('packages.ticketing.error.tickets.operator.delete_user_warn.view_tickets.tell_someone');
        }
        $error->setMessage($message);

        $event->addError($error);
    }

    private function checkTicketsMessages(UserpanelEvents\Users\BeforeDelete $event)
    {
        $user = $event->getUser();
        $hasTickets = (new Ticket())
                    ->join(TicketMessage::class, 'id', 'LEFT', 'ticket')
                    ->where('client', $user->id, '!=')
                    ->where('ticketing_tickets_msgs.user', $user->id)
                    ->has();
        if (!$hasTickets) {
            return;
        }
        $message = t('error.packages.ticketing.error.tickets.messages.user.delete_user_warn.message');
        $error = new Error('packages.ticketing.error.tickets.messages.user.delete_user_warn');
        $error->setType(Error::WARNING);
        if (Authorization::is_accessed('list')) {
            $message .= '<br> '.t('packages.ticketing.error.tickets.messages.user.delete_user_warn.view_tickets').' ';
            $error->setData([
                [
                    'txt' => '<i class="fa fa-search"></i> '.t('packages.ticketing.error.tickets.messages.user.delete_user_warn.view_tickets_btn'),
                    'type' => 'btn-warning',
                    'link' => url('ticketing', [
                        'message_sender' => $user->id,
                        'status' => implode(',', Ticket::STATUSES),
                    ]),
                ],
            ], 'btns');
        } else {
            $message .= '<br> '.t('packages.ticketing.error.tickets.messages.user.delete_user_warn.view_tickets.tell_someone');
        }
        $error->setMessage($message);

        $event->addError($error);
    }
}
