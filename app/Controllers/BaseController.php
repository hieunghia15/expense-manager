<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;

abstract class BaseController
{
    public function __construct(
        protected View $view
    ) {}

    protected function render(
        string $template,
        array $data = []
    ): void {
        echo $this->view->render(
            $template,
            $data
        );
    }
}
