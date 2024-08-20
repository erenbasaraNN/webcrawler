<?php
namespace App\Crawlers\Models;

class Issue
{
    private string $volume;
    private string $year;
    private string $number;
    private array $articles;

    public function __construct(string $volume, string $year, string $number, array $articles)
    {
        $this->volume = $volume;
        $this->year = $year;
        $this->number = $number;
        $this->articles = $articles;
    }

    public function getVolume(): string
    {
        return $this->volume;
    }

    public function getYear(): string
    {
        return $this->year;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getArticles(): array
    {
        return $this->articles;
    }
}
