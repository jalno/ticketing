<?php

namespace packages\ticketing\Controllers\Settings;

use packages\base\DB\Parenthesis;
use packages\base\NotFound;
use packages\base\Response;
use packages\base\View;
use packages\ticketing\Authorization;
use packages\ticketing\Contracts\ILabelManager;
use packages\ticketing\Label;
use packages\ticketing\Logs\Labels as Logs;
use packages\ticketing\ServiceProvider;
use packages\userpanel\Authentication;
use packages\userpanel\Controller;
use function packages\userpanel\url;
use themes\clipone\Views\Ticketing\Settings\Labels as Views;

class Labels extends Controller
{
    public ILabelManager $labelManager;
    protected bool $authentication = true;

    public function __construct(?ILabelManager $labelManager = null)
    {
        parent::__construct();

        $this->labelManager = $labelManager ?: (new ServiceProvider())->getLabelManager();
    }

    public function search(): Response
    {
        Authorization::haveOrFail('settings_labels_search');

        $view = View::byName(Views\Search::class);
        $this->response->setView($view);

        /**
         * @var array{id?:int,title?:string,status?:1|2,word?:string,comparison:equals|startswith|contains} $inputs
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
            'status' => [
                'type' => 'int',
                'values' => [Label::ACTIVE, Label::DEACTIVE],
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

        $query = new Label();

        foreach (['id', 'status'] as $item) {
            if (isset($inputs[$item])) {
                $query->where($item, $inputs[$item]);
            }
        }

        if (isset($inputs['title'])) {
            $query->where('title', $inputs['title'], $inputs['comparison']);
        }

        if (isset($inputs['word'])) {
            $parenthesis = new Parenthesis();

            foreach (['title', 'description'] as $item) {
                if (!isset($inputs[$item])) {
                    $parenthesis->orWhere($item, $inputs['word'], $inputs['comparison']);
                }
            }

            $query->where($parenthesis);
        }

        $query->pageLimit = $this->items_per_page;

        $labels = $query->paginate($this->page);

        $view->setDataList($labels);
        $view->setPaginate($this->page, $query->totalCount, $this->items_per_page);

        $this->response->setStatus(true);

        return $this->response;
    }

    public function add(): Response
    {
        Authorization::haveOrFail('settings_labels_add');

        $view = View::byName(Views\Add::class);
        $this->response->setView($view);

        $this->response->setStatus(true);

        return $this->response;
    }

    public function store(): Response
    {
        Authorization::haveOrFail('settings_labels_add');

        $inputs = $this->checkInputs([
            'title' => [
                'type' => 'string',
            ],
            'color' => [
                'type' => 'string',
            ],
            'description' => [
                'type' => 'string',
                'optional' => true,
            ],
        ]);

        $label = $this->labelManager->store($inputs);

        Logs\Add::create($label, Authentication::getUser());

        $this->response->setData([
            'id' => $label->getID(),
            'title' => $label->getTitle(),
            'description' => $label->getDescription() ?: '',
            'color' => $label->getColor(),
        ], 'label');
        $this->response->setStatus(true);

        return $this->response;
    }

    /**
     * @param array{id:int} $data
     */
    public function edit(array $data): Response
    {
        Authorization::haveOrFail('settings_labels_edit');

        $label = null;
        try {
            $label = $this->labelManager->getByID($data['id']);
        } catch (\Exception $e) {
            throw new NotFound();
        }

        $view = View::byName(Views\Edit::class);
        $this->response->setView($view);

        $view->setLabel($label);

        $this->response->setStatus(true);

        return $this->response;
    }

    /**
     * @param array{id:int} $data
     */
    public function update(array $data): Response
    {
        Authorization::haveOrFail('settings_labels_edit');

        $label = null;
        try {
            $label = $this->labelManager->getByID($data['id']);
        } catch (\Exception $e) {
            throw new NotFound();
        }

        $view = View::byName(Views\Edit::class);
        $this->response->setView($view);

        $view->setLabel($label);

        $inputs = $this->checkInputs([
            'title' => [
                'type' => 'string',
                'optional' => true,
            ],
            'color' => [
                'type' => 'string',
                'regex' => '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
                'empty' => true,
            ],
            'description' => [
                'type' => 'string',
                'optional' => true,
                'empty' => true,
            ],
            'status' => [
                'type' => 'int',
                'values' => [Label::ACTIVE, Label::DEACTIVE],
                'optional' => true,
            ],
        ]);

        $origin = $label;
        $label = $this->labelManager->update($label->getID(), $inputs);

        Logs\Edit::create($label, $origin, Authentication::getUser());

        $this->response->setStatus(true);

        return $this->response;
    }

    /**
     * @param array{id:int} $data
     */
    public function delete(array $data): Response
    {
        Authorization::haveOrFail('settings_labels_delete');

        $label = null;
        try {
            $label = $this->labelManager->getByID($data['id']);
        } catch (\Exception $e) {
            throw new NotFound();
        }

        $view = View::byName(Views\Delete::class);
        $this->response->setView($view);

        $view->setLabel($label);

        $this->response->setStatus(true);

        return $this->response;
    }

    /**
     * @param array{id:int} $data
     */
    public function terminate(array $data): Response
    {
        Authorization::haveOrFail('settings_labels_delete');

        $label = null;
        try {
            $label = $this->labelManager->getByID($data['id']);
        } catch (\Exception $e) {
            throw new NotFound();
        }

        $view = View::byName(Views\Delete::class);
        $this->response->setView($view);

        $view->setLabel($label);

        $this->labelManager->destroy($label->getID());

        Logs\Delete::create($label, Authentication::getUser());

        $this->response->setStatus(true);

        $this->response->Go(url('settings/ticketing/labels'));

        return $this->response;
    }
}
