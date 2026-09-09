<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreThesisRequest;
use App\Models\Domain;
use App\Models\Thesis;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class KnowledgeBaseController extends Controller
{
    /**
     * Landing page — overview of every group.
     */
    public function index(): View
    {
        return view('knowledge-base.home', [
            'domains' => $this->domainsOfType(Domain::TYPE_DOMAIN),
            'teams' => $this->domainsOfType(Domain::TYPE_TEAM),
            'archive' => Domain::query()->ofType(Domain::TYPE_ARCHIVE)->first(),
            'thesisCount' => Thesis::query()->count(),
        ]);
    }

    /**
     * List every managerial domain.
     */
    public function domains(): View
    {
        return view('knowledge-base.domain-index', [
            'pageTitle' => 'Dokumen Manajerial',
            'pageSubtitle' => 'Telusuri dokumentasi tata kelola TI berdasarkan domain.',
            'domains' => $this->domainsOfType(Domain::TYPE_DOMAIN),
            'routeName' => 'knowledge-base.domain',
        ]);
    }

    /**
     * List every team.
     */
    public function teams(): View
    {
        return view('knowledge-base.domain-index', [
            'pageTitle' => 'Dokumen Teknis',
            'pageSubtitle' => 'Telusuri dokumentasi teknis berdasarkan tim pengelola.',
            'domains' => $this->domainsOfType(Domain::TYPE_TEAM),
            'routeName' => 'knowledge-base.team',
        ]);
    }

    /**
     * Show a single domain (or team) with its categories and documents.
     */
    public function show(Domain $domain): View
    {
        abort_if($domain->type === Domain::TYPE_ARCHIVE, 404);

        $isAdmin = request()->user()?->isAdmin() ?? false;

        $domain->load(['categories' => fn ($query) => $query->ordered()->with([
            'documents' => function ($query) use ($isAdmin): void {
                if (! $isAdmin) {
                    $query->active();
                }
                $query->orderBy('title');
            },
        ])]);

        $backRoute = $domain->type === Domain::TYPE_TEAM
            ? 'knowledge-base.teams'
            : 'knowledge-base.domains';

        return view('knowledge-base.domain-show', [
            'domain' => $domain,
            'backRoute' => $backRoute,
            'documentCount' => $domain->categories->sum(fn ($category): int => $category->documents->count()),
        ]);
    }

    /**
     * Final assignment (Tugas Akhir) archive with filters and search.
     */
    public function archive(Request $request): View
    {
        $filters = [
            'search' => $request->string('search')->trim()->value(),
            'year' => $request->integer('year') ?: null,
            'study_program' => $request->string('study_program')->trim()->value() ?: null,
        ];

        $theses = Thesis::query()
            ->search($filters['search'])
            ->forYear($filters['year'])
            ->forStudyProgram($filters['study_program'])
            ->orderByDesc('year')
            ->orderBy('title')
            ->get();

        return view('knowledge-base.archive', [
            'theses' => $theses,
            'filters' => $filters,
            'years' => Thesis::query()->select('year')->distinct()->orderByDesc('year')->pluck('year'),
            'programs' => Thesis::query()->select('study_program')->distinct()->orderBy('study_program')->pluck('study_program'),
        ]);
    }

    /**
     * Store a new thesis submitted from the public form.
     */
    public function storeThesis(StoreThesisRequest $request): RedirectResponse
    {
        $data = $request->safe()->only([
            'title', 'author', 'university', 'study_program', 'year', 'drive_url',
        ]);

        if ($request->hasFile('document')) {
            $data['file_path'] = $request->file('document')->store('theses', 'public');
        }

        Thesis::create($data);

        return redirect()
            ->route('knowledge-base.archive')
            ->with('status', 'Arsip Tugas Akhir berhasil ditambahkan.');
    }

    /**
     * @return Collection<int, Domain>
     */
    private function domainsOfType(string $type)
    {
        return Domain::query()
            ->ofType($type)
            ->ordered()
            ->withCount(['categories', 'documents'])
            ->get();
    }
}
