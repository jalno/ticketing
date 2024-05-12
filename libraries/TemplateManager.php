<?php

namespace packages\ticketing;

use packages\base\Exception;
use packages\ticketing\Contracts\IServiceProvider;
use packages\ticketing\Contracts\ITemplate;
use packages\ticketing\Contracts\ITemplateManager;
use packages\ticketing\TicketMessage as Message;

class TemplateManager implements ITemplateManager
{
    public IServiceProvider $serviceProvider;

    public function __construct(IServiceProvider $serviceProvider)
    {
        $this->serviceProvider = $serviceProvider;
    }

    public function getByID(int $id): ITemplate
    {
        $query = new Template();
        $template = $query->byId($id);

        if (!$template) {
            throw new Exception('Can not find any template with id: '.$id);
        }

        return $template;
    }

    public function store(array $data): ITemplate
    {
        foreach (['title', 'content'] as $item) {
            if (!isset($data[$item]) or !$data[$item]) {
                throw new \InvalidArgumentException($item);
            }
        }

        if (isset($data['message_type']) and Template::REPLY == $data['message_type']) {
            unset($data['subject']);
        }

        $template = new Template();
        $template->title = $data['title'];
        $template->subject = $data['subject'] ?? null;
        $template->department_id = $data['department'] ?? null;
        $template->content = $data['content'];
        $template->message_type = $data['message_type'] ?? null;
        $template->message_format = $data['message_format'] ?? Message::html;
        $template->status = $data['status'] ?? Template::ACTIVE;
        $result = $template->save();

        if (!$result) {
            throw new Exception('Can not store template');
        }

        return $template;
    }

    public function update(int $id, array $data): ITemplate
    {
        $template = $this->getByID($id);

        if (
            (isset($data['message_type']) and Template::REPLY == $data['message_type'])
            or Template::REPLY == $template->getMessageType()
        ) {
            $data['subject'] = '';
        }

        foreach (['title', 'content', 'message_format', 'status'] as $item) {
            if (isset($data[$item]) and $template->{$item} != $data[$item]) {
                $changes['new'][$item] = $data[$item];
                $template->{$item} = $data[$item];
            }
        }

        foreach (['subject', 'message_format', 'message_type'] as $item) {
            if (array_key_exists($item, $data) and $data[$item] != $template->{$item}) {
                $template->{$item} = $data[$item];
            }
        }

        if (array_key_exists('department', $data) and $data['department'] != $template->department_id) {
            $template->department_id = $data['department'];
        }

        $template->save();

        return $template;
    }

    public function delete(int $id): ITemplate
    {
        $template = $this->getByID($id);

        $template->delete();

        return $template;
    }
}
