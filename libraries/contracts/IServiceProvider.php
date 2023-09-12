<?php

namespace packages\ticketing\contracts;

interface IServiceProvider
{
    public function getTemplateManager(): ITemplateManager;
}
