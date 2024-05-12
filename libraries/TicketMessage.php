<?php

namespace packages\ticketing;

use packages\base\DB\DBObject;
use packages\base\Exception;
use packages\userpanel\Date;
use packages\userpanel\User;

class TicketMessage extends DBObject
{
    public const unread = 0;
    public const read = 1;
    public const html = 'html';
    public const markdown = 'markdown';

    public static function convertContent(string $content, string $format): string
    {
        switch ($format) {
            case self::markdown:
                return (new Parsedown())->text($content);
            case self::html:
                $content = str_replace(["\r\n", "\n\r", "\r"], "\n", $content);
                $content = preg_replace('@([https|http|ftp]+://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" rel="nofollow">$1</a>', $content);

                return implode("\n", array_map(fn (string $line) => '<p dir="auto">'.("\n" !== $line ? $line : '&nbsp;')."</p>\n", explode("\n", $content)));
            default:
                throw new Exception('Unknown format '.$format.' for convert');
        }
    }

    protected $dbTable = 'ticketing_tickets_msgs';
    protected $primaryKey = 'id';
    protected $dbFields = [
        'ticket' => ['type' => 'int', 'required' => true],
        'date' => ['type' => 'int', 'required' => true],
        'user' => ['type' => 'int', 'required' => true],
        'text' => ['type' => 'text', 'required' => true],
        'format' => ['type' => 'text', 'required' => true],
        'status' => ['type' => 'int', 'required' => true],
    ];
    protected $relations = [
        'ticket' => ['hasOne', 'packages\\ticketing\\ticket', 'ticket'],
        'user' => ['hasOne', 'packages\\userpanel\\user', 'user'],
        'files' => ['hasMany', 'packages\\ticketing\\ticket_file', 'message'],
    ];

    public function getContent(): string
    {
        return self::convertContent($this->text, $this->format);
    }

    protected function preLoad($data)
    {
        if (!isset($data['format'])) {
            $user = User::where('id', $data['user'])->getOne();
            $data['format'] = $user->option('ticketing_editor');
            if (!$data['format']) {
                $data['format'] = self::html;
            }
        }
        if (!isset($data['date'])) {
            $data['date'] = Date::time();
        }

        return $data;
    }
    protected $tmpfiles = [];

    protected function addFile($filedata)
    {
        $file = new TicketFile($filedata);
        if ($this->isNew) {
            $this->tmpfiles[] = $file;

            return true;
        } else {
            $file->message = $this->id;
            $return = $file->save();
            if (!$return) {
                return false;
            }

            return $return;
        }
    }

    public function save($data = null)
    {
        if ($return = parent::save($data)) {
            foreach ($this->tmpfiles as $file) {
                $file->message = $this->id;
                $file->save();
            }
            $this->tmpfiles = [];
        }

        return $return;
    }

    public function delete()
    {
        foreach ($this->files as $file) {
            $file->delete();
        }

        return parent::delete();
    }
}
