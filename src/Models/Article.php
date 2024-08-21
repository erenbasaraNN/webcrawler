<?php

namespace App\Models;

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
    private ?string $en_abstract;
    private ?string $en_keywords;


    public function __construct(
        string $title,
        ?string $en_title,
        ?string $abstract,
        array $authors,
        ?string $pdfUrl,
        ?string $firstPage,
        ?string $lastPage,
        ?string $keywords,
        ?string $primaryLanguage,
        ?string $en_abstract = null,
        ?string $en_keywords = null
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
        $this->en_abstract = $en_abstract;
        $this->en_keywords = $en_keywords;
    }

    public function getTitle(): string { return $this->title; }
    public function getEnglishTitle(): ?string { return $this->en_title; }
    public function getEnglishAbstract(): ?string { return $this->en_abstract; }
    public function getEnglishKeywords(): ?string { return $this->en_keywords; }
    public function getAbstract(): ?string { return $this->abstract; }
    public function getAuthors(): array { return $this->authors; }
    public function getPdfUrl(): ?string { return $this->pdfUrl; }
    public function getFirstPage(): ?string { return $this->firstPage; }
    public function getLastPage(): ?string { return $this->lastPage; }
    public function getKeywords(): ?string { return $this->keywords; }
    public function getPrimaryLanguage(): ?string { return $this->primaryLanguage; }



}
