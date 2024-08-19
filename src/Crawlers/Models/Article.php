<?php

namespace App\Crawlers\Models;

class Article
{
    private string $title;
    private ?string $en_title;
    private ?string $abstract;
    private array $authors;
    private ?string $pdfUrl;
    private ?string $firstPage;
    private ?string $lastPage;
    private ?string $keywords;
    private ?string $primaryLanguage;

    public function __construct(
        string $title,
        ?string $en_title,
        ?string $abstract,
        array $authors,
        ?string $pdfUrl,
        ?string $firstPage,
        ?string $lastPage,
        ?string $keywords,
        ?string $primaryLanguage
    ) {
        $this->title = $title;
        $this->en_title = $en_title;
        $this->abstract = $abstract;
        $this->authors = $authors;
        $this->pdfUrl = $pdfUrl;
        $this->firstPage = $firstPage;
        $this->lastPage = $lastPage;
        $this->keywords = $keywords;
        $this->primaryLanguage = $primaryLanguage;
    }

    public function getTitle(): string { return $this->title; }
    public function getEnTitle(): ?string { return $this->en_title; }
    public function getAbstract(): ?string { return $this->abstract; }
    public function getAuthors(): array { return $this->authors; }
    public function getPdfUrl(): ?string { return $this->pdfUrl; }
    public function getFirstPage(): ?string { return $this->firstPage; }
    public function getLastPage(): ?string { return $this->lastPage; }
    public function getKeywords(): ?string { return $this->keywords; }
    public function getPrimaryLanguage(): ?string { return $this->primaryLanguage; }
}
