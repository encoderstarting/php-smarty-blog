<?php

declare(strict_types=1);

namespace App\Core;

use Smarty\Smarty;

final class View
{
    private Smarty $smarty;

    public function __construct(string $rootPath)
    {
        $this->smarty = new Smarty();
        $this->smarty->setTemplateDir($rootPath . '/templates');
        $this->smarty->setCompileDir($rootPath . '/storage/templates_c');
        $this->smarty->setCacheDir($rootPath . '/storage/cache');
    }

    /** @param array<string, mixed> $data */
    public function render(string $template, array $data = []): void
    {
        $this->smarty->assign($data);
        $this->smarty->display($template);
    }
}

