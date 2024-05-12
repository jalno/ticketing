<?php

namespace packages\ticketing\Contracts;

interface IServiceProvider
{
    public function getTemplateManager(): ITemplateManager;
}
