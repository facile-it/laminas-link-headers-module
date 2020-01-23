<?php

declare(strict_types=1);

namespace Facile\LaminasLinkHeadersModule\Functional;

use Laminas\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $this->layout()->setTemplate('index');
    }
}
