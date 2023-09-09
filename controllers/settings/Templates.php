<?php

namespace packages\ticketing\controllers\settings;

use packages\base\DB\Parenthesis;
use packages\base\NotFound;
use packages\base\Response;
use packages\base\View;
use packages\ticketing\Authorization;
use packages\ticketing\contracts\ITemplateManager;
use packages\ticketing\Department;
use packages\ticketing\logs\templates as logs;
use packages\ticketing\ServiceProvider;
use packages\ticketing\Template;
use packages\ticketing\ticket_message as Message;
use packages\userpanel\Authentication;
use packages\userpanel\Controller;
use function packages\userpanel\url;
use themes\clipone\views\ticketing\settings\templates as views;

class Templates extends Controller
{
    public ITemplateManager $templateManager;
    protected bool $authentication = true;

    public function __construct(?ITemplateManager $templateManager = null)
    {
        parent::__construct();

        $this->templateManager = $templateManager ?: (new ServiceProvider())->getTemplateManager();
    }

    public function search(): Response
    {
        Authorization::haveOrFail('settings_templates_search');

        $view = View::byName(views\Search::class);
        $this->response->setView($view);

        /**
         * @var array{id?:int,department?:Department,status?:Template::ACTIVE|Template::DEACTIVE,message_type?:Template::ADD|Template::REPLY|empty-string,word?:string} $inputs
         */
        $inputs = $this->checkInputs([
            'id' => [
                'type' => 'int',
                'optional' => true,
            ],
            'title' => [
                'type' => 'string',
                'optional' => true,
            ],
            'subject' => [
                'type' => 'string',
                'optional' => true,
            ],
            'department' => [
                'type' => Department::class,
                'query' => function (Department $query) {
                    $query->where('status', Department::ACTIVE);
                },
                'optional' => true,
            ],
            'status' => [
                'type' => 'int',
                'values' => [Template::ACTIVE, Template::DEACTIVE],
                'optional' => true,
            ],
            'message_type' => [
                'type' => ['int', 'string'],
                'values' => [Template::ADD, Template::REPLY, Template::ADD.','.Template::REPLY],
                'optional' => true,
            ],
            'message_format' => [
                'type' => 'int',
                'values' => [Message::html, Message::markdown],
                'optional' => true,
            ],
            'word' => [
                'type' => 'string',
                'optional' => true,
            ],
            'comparison' => [
                'values' => ['equals', 'startswith', 'contains'],
                'default' => 'contains',
                'optional' => true,
            ],
        ]);

        $query = new Template();

        foreach (['id', 'status'] as $item) {
            if (isset($inputs[$item])) {
                $query->where($item, $inputs[$item]);
            }
        }

        foreach (['title', 'subject'] as $item) {
            if (isset($inputs[$item])) {
                $query->where($item, $inputs[$item], $inputs['comparison']);
            }
        }

        if (isset($inputs['department'])) {
            $query->where('department_id', $inputs['department']->id);
        }

        if (isset($inputs['message_type'])) {
            if ($inputs['message_type'] == Template::ADD.','.Template::REPLY) {
                $query->where('message_type', null, 'is');
            } else {
                $query->where('message_type', $inputs['message_type']);
            }
        }

        if (isset($inputs['word'])) {
            $parenthesis = new Parenthesis();

            foreach (['title', 'subject', 'content'] as $item) {
                if (!isset($inputs[$item])) {
                    $parenthesis->orWhere($item, $inputs['word'], $inputs['comparison']);
                }
            }

            $query->where($parenthesis);
        }

        $query->pageLimit = $this->items_per_page;

        $templates = $query->paginate($this->page);

        $view->setDataList($templates);
        $view->setPaginate($this->page, $query->totalCount, $this->items_per_page);

        $this->response->setStatus(true);

        return $this->response;
    }

    public function add(): Response
    {
        Authorization::haveOrFail('settings_templates_add');

        $view = View::byName(views\Add::class);
        $this->response->setView($view);

        $this->response->setStatus(true);

        return $this->response;
    }

    public function store(): Response
    {
        Authorization::haveOrFail('settings_templates_add');

        $view = View::byName(views\Add::class);
        $this->response->setView($view);

        $inputs = $this->checkInputs([
            'title' => [
                'type' => 'string',
            ],
            'subject' => [
                'type' => 'string',
                'optional' => true,
            ],
            'department' => [
                'type' => Department::class,
                'query' => function (Department $query) {
                    $query->where('status', Department::ACTIVE);
                },
                'optional' => true,
            ],
            'content' => [
                'type' => 'string',
            ],
            'message_type' => [
                'type' => 'int',
                'values' => [Template::ADD, Template::REPLY],
                'optional' => true,
            ],
            'format' => [
                'type' => 'int',
                'values' => [Message::html, Message::markdown],
                'default' => Message::html,
                'optional' => true,
            ],
        ]);

        $template = $this->templateManager->store($inputs);

        logs\Add::create($template, Authentication::getUser());

        $this->response->setStatus(true);

        return $this->response;
    }

    /**
     * @param array{id:int} $data
     */
    public function edit(array $data): Response
    {
        Authorization::haveOrFail('settings_templates_edit');

        $template = null;
        try {
            $template = $this->templateManager->getByID($data['id']);
        } catch (\Exception $e) {
            throw new NotFound();
        }

        $view = View::byName(views\Edit::class);
        $this->response->setView($view);

        $view->setTemplate($template);

        $this->response->setStatus(true);

        return $this->response;
    }

    /**
     * @param array{id:int} $data
     */
    public function update(array $data): Response
    {
        Authorization::haveOrFail('settings_templates_edit');

        $template = null;
        try {
            $template = $this->templateManager->getByID($data['id']);
        } catch (\Exception $e) {
            throw new NotFound();
        }

        $view = View::byName(views\Edit::class);
        $this->response->setView($view);

        $view->setTemplate($template);

        $view = View::byName(views\Edit::class);
        $this->response->setView($view);

        $inputs = $this->checkInputs([
            'title' => [
                'type' => 'string',
                'optional' => true,
            ],
            'subject' => [
                'type' => 'string',
                'optional' => true,
                'empty' => true,
            ],
            'department' => [
                'type' => Department::class,
                'query' => function (Department $query) {
                    $query->where('status', Department::ACTIVE);
                },
                'optional' => true,
                'empty' => true,
            ],
            'content' => [
                'type' => 'string',
                'optional' => true,
            ],
            'message_type' => [
                'type' => 'int',
                'values' => [Template::ADD, Template::REPLY],
                'optional' => true,
                'empty' => true,
            ],
            'message_format' => [
                'type' => 'string',
                'values' => [Message::html, Message::markdown],
                'default' => $template->getMessageFormat(),
                'optional' => true,
            ],
        ]);

        if (isset($inputs['deplartment'])) {
            $inputs['department'] = $inputs['department']->id;
        }

        $origin = $template;
        $template = $this->templateManager->update($template->getID(), $inputs);

        logs\Edit::create($template, $origin, Authentication::getUser());

        $this->response->setStatus(true);

        return $this->response;
    }

    /**
     * @param array{id:int} $data
     */
    public function delete(array $data): Response
    {
        Authorization::haveOrFail('settings_templates_delete');

        $template = null;
        try {
            $template = $this->templateManager->getByID($data['id']);
        } catch (\Exception $e) {
            throw new NotFound();
        }

        $view = View::byName(views\Delete::class);
        $this->response->setView($view);

        $view->setTemplate($template);

        $this->response->setStatus(true);

        return $this->response;
    }

    /**
     * @param array{id:int} $data
     */
    public function terminate(array $data): Response
    {
        Authorization::haveOrFail('settings_templates_delete');

        $template = null;
        try {
            $template = $this->templateManager->getByID($data['id']);
        } catch (\Exception $e) {
            throw new NotFound();
        }

        $view = View::byName(views\Delete::class);
        $this->response->setView($view);

        $view->setTemplate($template);

        $this->templateManager->delete($template->getID());

        logs\Delete::create($template, Authentication::getUser());

        $this->response->setStatus(true);

        $this->response->Go(url('settings/ticketing/templates'));

        return $this->response;
    }
}
