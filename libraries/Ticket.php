<?php

namespace packages\ticketing;

use packages\base\DB;
use packages\base\DB\DBObject;
use packages\base\Options;
use packages\userpanel\Authentication;
use packages\userpanel\User;

class Ticket extends DBObject
{
    public const unread = 1;
    public const read = 2;
    public const in_progress = 3;
    public const answered = 4;
    public const closed = 5;

    public const instantaneous = 1;
    public const important = 2;
    public const ordinary = 3;

    public const canSendMessage = 0;

    public const SEND_NOTIFICATION_USER_OPTION_NAME = 'ticketing_send_notification';

    public const STATUSES = [
        self::unread,
        self::read,
        self::in_progress,
        self::answered,
        self::closed,
    ];
    public const PRIORITIES = [
        self::instantaneous,
        self::important,
        self::ordinary,
    ];

    public static function sendNotificationOnSendTicket(?User $user = null): ?bool
    {
        if ($user) {
            $res = $user->getOption(Ticket::SEND_NOTIFICATION_USER_OPTION_NAME);
            if (!is_null($res)) {
                return $res;
            }
        }

        return Options::get('packages.ticketing.send_notification_on_send_ticket');
    }

    public static function countUnreadTicketCountByUser(?User $user = null): int
    {
        if (!$user) {
            $user = Authentication::getUser();
        }
        $accessedDeparments = [];
        $types = $user->childrenTypes();
        if ($types) {
            foreach ((new Department())->get() as $department) {
                if ($department->users) {
                    if (in_array($user->id, $department->users)) {
                        $accessedDeparments[] = $department->id;
                    }
                } else {
                    $accessedDeparments[] = $department->id;
                }
            }
            if (!$accessedDeparments) {
                return 0;
            }
        }
        $ticket = new Ticket();
        $count = 0;
        if ($types) {
            DB::join('userpanel_users', 'userpanel_users.id=ticketing_tickets.client', 'INNER');
            $ticket->where('userpanel_users.type', $types, 'IN');
            $ticket->where('ticketing_tickets.status', [self::unread, self::read, self::in_progress], 'IN');
            $ticket->where('ticketing_tickets.department', $accessedDeparments, 'IN');

            return $ticket->count();
        }
        DB::join('ticketing_tickets_msgs', 'ticketing_tickets_msgs.ticket=ticketing_tickets.id', 'INNER');
        DB::joinWhere('ticketing_tickets_msgs', 'ticketing_tickets_msgs.status', TicketMessage::unread);
        $ticket->where('ticketing_tickets.client', $user->id);

        return $ticket->count();
    }

    protected $dbTable = 'ticketing_tickets';
    protected $primaryKey = 'id';
    protected $dbFields = [
        'create_at' => ['type' => 'int', 'required' => true],
        'reply_at' => ['type' => 'int', 'required' => true],
        'title' => ['type' => 'text', 'required' => true],
        'priority' => ['type' => 'int', 'required' => true],
        'department' => ['type' => 'int', 'required' => true],
        'client' => ['type' => 'int', 'required' => true],
        'operator_id' => ['type' => 'int'],
        'status' => ['type' => 'int', 'required' => true],
    ];
    protected $relations = [
        'message' => ['hasMany', 'packages\\ticketing\\ticket_message', 'ticket'],
        'params' => ['hasMany', 'packages\\ticketing\\ticket_param', 'ticket'],
        'client' => ['hasOne', User::class, 'client'],
        'department' => ['hasOne', 'packages\\ticketing\\department', 'department'],
        'operator' => ['hasOne', User::class, 'operator_id'],
    ];

    protected function preLoad($data)
    {
        if (!isset($data['create_at'])) {
            $data['create_at'] = time();
        }
        if (!isset($data['reply_at'])) {
            $data['reply_at'] = time();
        }

        return $data;
    }

    protected $tmpmessages = [];
    protected $tmparams = [];

    protected function addMessage($messagedata)
    {
        $message = new TicketMessage($messagedata);
        if ($this->isNew) {
            $this->tmpmessages[] = $message;

            return true;
        } else {
            $message->ticket = $this->id;
            $return = $message->save();
            if (!$return) {
                return false;
            }

            return $return;
        }
    }

    public function param($name)
    {
        if (!$this->id) {
            return isset($this->tmparams[$name]) ? $this->tmparams[$name]->value : null;
        } else {
            foreach ($this->params as $param) {
                if ($param->name == $name) {
                    return $param->value;
                }
            }

            return false;
        }
    }

    public function setParam($name, $value)
    {
        $param = false;
        foreach ($this->params as $p) {
            if ($p->name == $name) {
                $param = $p;
                break;
            }
        }
        if (!$param) {
            $param = new TicketParam([
                'name' => $name,
                'value' => $value,
            ]);
        } else {
            $param->value = $value;
        }

        if (!$this->id or $this->isNew) {
            $this->tmparams[$name] = $param;
        } else {
            $param->ticket = $this->id;

            return $param->save();
        }
    }

    public function save($data = null)
    {
        if ($return = parent::save($data)) {
            foreach ($this->tmparams as $param) {
                $param->ticket = $this->id;
                $param->save();
            }
            $this->tmparams = [];
            foreach ($this->tmpmessages as $message) {
                $message->ticket = $this->id;
                $message->save();
            }
            $this->tmpmessages = [];
        }

        return $return;
    }

    public function delete()
    {
        DB::join('ticketing_tickets_msgs msg', 'msg.id=ticketing_files.message', 'LEFT');
        DB::where('msg.ticket', $this->id);
        $files = DB::get('ticketing_files', null, 'ticketing_files.*');
        foreach ($files as $file) {
            $file = new TicketFile($file);
            $file->delete();
        }

        return parent::delete();
    }

    public function getMessageCount(): int
    {
        $message = new TicketMessage();
        $message->where('ticket', $this->id);

        return max($message->count() - 1, 0);
    }

    public function hasUnreadMessage(): bool
    {
        DB::join('ticketing_tickets', 'ticketing_tickets.id=ticketing_tickets_msgs.ticket', 'INNER');
        DB::joinWhere('ticketing_tickets', 'ticketing_tickets.id', $this->id);
        $message = new TicketMessage();
        $message->where('ticketing_tickets_msgs.status', TicketMessage::unread);
        $message->where('ticketing_tickets_msgs.user', 'ticketing_tickets.client', '!=');

        return $message->has();
    }

    /**
     * @return ILabel[]
     */
    public function getLabels(): array
    {
        DB::join('ticketing_tickets_labels', 'ticketing_tickets_labels.label_id=ticketing_labels.id', 'inner');

        $query = new Label();
        $query->where('ticketing_labels.status', Label::ACTIVE);
        $query->where('ticketing_tickets_labels.ticket_id', $this->id);

        return $query->get(null, 'ticketing_labels.*');
    }

    /**
     * @param int[] $ids
     *
     * @return ILabel[] ticket labels
     */
    public function setLabels(array $ids): array
    {
        $labels = [];
        $mustBeDeleted = [];
        $mustBeAdded = [];

        $query = DB::where('ticketing_tickets_labels.ticket_id', $this->id);
        $existsLabelsIds = array_column($query->get('ticketing_tickets_labels', null, 'ticketing_tickets_labels.label_id'), 'label_id');

        if ($ids) {
            $query = new Label();
            $query->where('id', $ids, 'in');
            $query->where('status', Label::ACTIVE);
            $labels = $query->get(null, ['id', 'title', 'color']);

            $mustBeAdded = array_values(array_diff($ids, $existsLabelsIds));
            $mustBeDeleted = array_values(array_diff($existsLabelsIds, $ids));
        } else {
            $mustBeDeleted = $ids;
        }

        if ($mustBeDeleted or !$ids) {
            $query = DB::where('ticketing_tickets_labels.ticket_id', $this->id);
            if ($mustBeDeleted) {
                $query->where('ticketing_tickets_labels.label_id', $mustBeDeleted, 'in');
            }
            $query->delete('ticketing_tickets_labels');
        }

        if ($mustBeAdded) {
            DB::insertMulti('ticketing_tickets_labels', array_map(fn ($id) => ['label_id' => $id, 'ticket_id' => $this->id], $mustBeAdded));
        }

        return $labels;
    }

    /**
     * @param int[] $ids
     *
     * @return ILabel[] deleted labels
     */
    public function deleteLabels(array $ids): array
    {
        $query = new Label();
        $query->where('id', $ids, 'in');
        $labels = $query->get(null, ['id', 'title', 'color']);

        $ids = array_column($labels, 'id');

        $query = DB::where('ticketing_tickets_labels.ticket_id', $this->id);
        if ($ids) {
            $query->where('ticketing_tickets_labels.label_id', $ids, 'in');
        }
        $query->delete('ticketing_tickets_labels');

        return $labels;
    }
}
