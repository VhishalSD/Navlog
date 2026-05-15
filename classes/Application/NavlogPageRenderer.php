<?php

class NavlogPageRenderer
{
    public static function render(array $viewData): string
    {
        ob_start();

        extract($viewData);
        $postData = $viewData['postData'] ?? [];

        require __DIR__ . '/../../views/navlog-page.php';

        return ob_get_clean();
    }
}
