<?php

namespace App\Support;

use App\Models\Document;
use App\Models\Domain;
use App\Models\Thesis;
use Illuminate\Support\Collection;

class SearchIndex
{
    /**
     * Build a lightweight, JSON-serialisable index for the global search.
     *
     * @return list<array{label: string, meta: string, group: string, url: string}>
     */
    public static function build(): array
    {
        return collect()
            ->merge(self::domains())
            ->merge(self::documents())
            ->merge(self::theses())
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, array{label: string, meta: string, group: string, url: string}>
     */
    private static function domains(): Collection
    {
        return Domain::query()
            ->where('type', '!=', Domain::TYPE_ARCHIVE)
            ->get()
            ->map(fn (Domain $domain): array => [
                'label' => $domain->name,
                'meta' => $domain->type === Domain::TYPE_TEAM ? 'Tim' : 'Domain',
                'group' => $domain->type === Domain::TYPE_TEAM ? 'Tim' : 'Domain',
                'url' => $domain->type === Domain::TYPE_TEAM
                    ? route('knowledge-base.team', $domain)
                    : route('knowledge-base.domain', $domain),
            ]);
    }

    /**
     * @return Collection<int, array{label: string, meta: string, group: string, url: string}>
     */
    private static function documents(): Collection
    {
        $isAdmin = request()->user()?->isAdmin() ?? false;

        return Document::query()
            ->when(! $isAdmin, fn ($query) => $query->active())
            ->with('category.domain')
            ->get()
            ->map(function (Document $document): array {
                $domain = $document->category->domain;

                $url = $domain->type === Domain::TYPE_TEAM
                    ? route('knowledge-base.team', $domain)
                    : route('knowledge-base.domain', $domain);

                return [
                    'label' => $document->title,
                    'meta' => $domain->name.' · '.$document->category->name,
                    'group' => 'Dokumen',
                    'url' => $url.'#'.$document->category->slug,
                ];
            });
    }

    /**
     * @return Collection<int, array{label: string, meta: string, group: string, url: string}>
     */
    private static function theses(): Collection
    {
        return Thesis::query()
            ->get()
            ->map(fn (Thesis $thesis): array => [
                'label' => $thesis->title,
                'meta' => $thesis->author.' · '.$thesis->study_program.' · '.$thesis->year,
                'group' => 'Arsip TA',
                'url' => route('knowledge-base.archive', ['search' => $thesis->title]),
            ]);
    }
}
