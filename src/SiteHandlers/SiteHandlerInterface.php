<?php
namespace App\SiteHandlers;

interface SiteHandlerInterface {
    public function handle(string $url): array;
}
