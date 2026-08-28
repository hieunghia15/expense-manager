<?php

declare(strict_types=1);

namespace App\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class View
{
    private Environment $twig;

    public function __construct(string $viewsPath)
    {
        $loader = new FilesystemLoader($viewsPath);

        $this->twig = new Environment(
            $loader,
            [
                'cache' => false,
                'autoescape' => 'html',
                'strict_variables' => true,
            ]
        );
    }

    public function render(
        string $template,
        array $data = []
    ): string {
        return $this->twig->render($template, $data);
    }
}
