<?php

class PageRequestView
{
    public function __construct(
        private array $server,
        private array $post,
        private array $get,
        private array &$session
    ) {
    }

    public static function fromGlobals(array $server, array $post, array $get, array &$session): self
    {
        return new self($server, $post, $get, $session);
    }

    public static function fromCurrentRequest(): self
    {
        return new self($_SERVER, $_POST, $_GET, $_SESSION);
    }

    public function serverData(): array
    {
        return $this->server;
    }

    public function postData(): array
    {
        return $this->post;
    }

    public function getData(): array
    {
        return $this->get;
    }

    public function &sessionData(): array
    {
        return $this->session;
    }
}
