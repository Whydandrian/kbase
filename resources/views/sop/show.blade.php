@php($user = auth()->user())
@php($pemeriksa = $sop->approvalFor('pemeriksa'))
@php($canApprove = $user && ($pemeriksa && $pemeriksa->user_id === $user->id || $user->isAdmin()))
<x-knowledge-base.layout :title="$sop->nama_sop . ' — SOP'">
    <main class="page">
        <div class="page-inner">
            <div class="breadcrumb">
                <a href="{{ route('knowledge-base') }}">Knowledge base</a>
                <x-icon name="chevron-right" :size="14" />
                <a href="{{ route('sop.index') }}">SOP</a>
                <x-icon name="chevron-right" :size="14" />
                <strong>{{ $sop->nama_sop }}</strong>
            </div>

            <section class="intro-row">
                <div>
                    <h1>{{ $sop->nama_sop }}</h1>
                    <p class="intro-copy">Nomor SOP: {{ $sop->nomor_sop }} · Status: <strong>{{ ucfirst($sop->status) }}</strong></p>
                </div>
                <div class="sop-toolbar">
                    <a class="secondary-button" href="{{ route('sop.pdf', $sop) }}"><x-icon name="arrow-right" :size="15" /> Export PDF</a>
                    @auth
                        <a class="secondary-button" href="{{ route('sop.edit', $sop) }}"><x-icon name="settings" :size="15" /> Edit</a>
                        <form method="POST" action="{{ route('sop.destroy', $sop) }}" onsubmit="return confirm('Hapus SOP ini?');">
                            @csrf @method('DELETE')
                            <button class="secondary-button" type="submit"><x-icon name="x" :size="15" /> Hapus</button>
                        </form>
                    @endauth
                </div>
            </section>

            @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
            @if (session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif

            {{-- ===== Identitas SOP ===== --}}
            <div class="sop-doc">
                <table class="sop-identity">
                    <tr>
                        <td class="sop-identity-org" rowspan="4">
                            <strong>{{ $sop->kementerian }}</strong><br>
                            {{ $sop->institusi }}<br>
                            {{ $sop->unit_pembuat }}
                        </td>
                        <th>Nomor SOP</th><td>{{ $sop->nomor_sop }}</td>
                    </tr>
                    <tr><th>Tgl. Pembuatan</th><td>{{ $sop->tgl_pembuatan?->format('d F Y') }}</td></tr>
                    <tr><th>Tgl. Revisi</th><td>{{ $sop->tgl_revisi?->format('d F Y') }}</td></tr>
                    <tr><th>Tgl. Efektif</th><td>{{ $sop->tgl_efektif?->format('d F Y') }}</td></tr>
                    <tr><th>Nama SOP</th><td colspan="3">{{ $sop->nama_sop }}</td></tr>
                </table>

                {{-- ===== Penyusun / Pemeriksa / Pengesahan ===== --}}
                <table class="sop-approvals">
                    <thead>
                        <tr><th></th>
                            @foreach (\App\Models\Sop::APPROVAL_ROLES as $role)
                                <th>{{ ucfirst($role) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr><th>Tanggal</th>
                            @foreach (\App\Models\Sop::APPROVAL_ROLES as $role)
                                <td>{{ $sop->approvalFor($role)?->tanggal?->format('d M Y') }}</td>
                            @endforeach
                        </tr>
                        <tr><th>Nama</th>
                            @foreach (\App\Models\Sop::APPROVAL_ROLES as $role)
                                <td>{{ $sop->approvalFor($role)?->nama }}</td>
                            @endforeach
                        </tr>
                        <tr><th>Jabatan</th>
                            @foreach (\App\Models\Sop::APPROVAL_ROLES as $role)
                                <td>{{ $sop->approvalFor($role)?->jabatan }}</td>
                            @endforeach
                        </tr>
                        <tr><th>Tanda tangan</th>
                            @foreach (\App\Models\Sop::APPROVAL_ROLES as $role)
                                @php($ap = $sop->approvalFor($role))
                                <td class="sop-sign">
                                    @if ($ap && $ap->hasSignature())
                                        <img src="{{ route('sop.file', [$sop, 'signature', $ap->id]) }}" alt="TTD {{ $role }}" class="sop-signature">
                                    @elseif ($role === 'pengesahan' && $sop->hasFinalDocument())
                                        <span class="sop-signed-note">Ditandatangani &amp; distempel (dokumen final)</span>
                                    @else
                                        <span class="sop-unsigned">Belum ditandatangani</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>

                {{-- ===== Approve (pemeriksa) ===== --}}
                @if ($canApprove && $pemeriksa && ! $pemeriksa->hasSignature())
                    <form method="POST" action="{{ route('sop.approve', $sop) }}" class="sop-approve-bar">
                        @csrf
                        <span>Anda ditunjuk sebagai <strong>pemeriksa</strong>. Setujui untuk membubuhkan tanda tangan Anda.</span>
                        <button type="submit" class="primary-button"><x-icon name="shield-check" :size="15" /> Setujui &amp; Tanda Tangani</button>
                    </form>
                @endif

                {{-- ===== Naratif (tujuan, ruang lingkup, istilah) ===== --}}
                @foreach (['tujuan' => 'Tujuan', 'ruang_lingkup' => 'Ruang Lingkup', 'istilah' => 'Istilah dan Definisi'] as $type => $label)
                    @if ($sop->itemsOfType($type)->isNotEmpty())
                        <div class="sop-box">
                            <h3>{{ $label }}</h3>
                            <ul>
                                @foreach ($sop->itemsOfType($type) as $item)
                                    <li>{{ $item->content }}@if ($item->url) <a href="{{ $item->url }}" target="_blank" rel="noopener">(tautan)</a>@endif</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endforeach

                {{-- ===== Item grup (dasar hukum, dst) ===== --}}
                <div class="sop-grid-2">
                    @foreach (['dasar_hukum' => 'Dasar Hukum', 'kualifikasi' => 'Kualifikasi Pelaksana', 'keterkaitan' => 'Keterkaitan', 'peralatan' => 'Peralatan/Perlengkapan', 'peringatan' => 'Peringatan', 'pencatatan' => 'Pencatatan dan Pendataan'] as $type => $label)
                        <div class="sop-box">
                            <h3>{{ $label }}</h3>
                            <ul>
                                @forelse ($sop->itemsOfType($type) as $item)
                                    <li>
                                        {{ $item->content }}
                                        @if ($item->url)<a href="{{ $item->url }}" target="_blank" rel="noopener">(tautan)</a>@endif
                                    </li>
                                @empty
                                    <li class="sop-empty">—</li>
                                @endforelse
                            </ul>
                        </div>
                    @endforeach
                </div>

                {{-- ===== Flowchart ===== --}}
                @if ($sop->flowSteps->isNotEmpty())
                    <div class="sop-box">
                        <h3>Flowchart</h3>
                        <div class="table-wrap">
                            <table class="thesis-table sop-flow">
                                <thead>
                                    <tr>
                                        <th>No</th><th>Kegiatan</th><th>Pelaksana</th>
                                        <th>Kelengkapan</th><th>Waktu</th><th>Output</th><th>Diagram</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($sop->flowSteps as $step)
                                        <tr>
                                            <td>{{ $step->step_no }}</td>
                                            <td>{{ $step->kegiatan }} @if ($step->is_decision)<span class="tag">keputusan</span>@endif</td>
                                            <td>{{ $step->pelaksana }}</td>
                                            <td>{{ $step->kelengkapan }}</td>
                                            <td>{{ $step->waktu }}</td>
                                            <td>{{ $step->output }}</td>
                                            <td>
                                                @if ($step->hasImage())
                                                    <img src="{{ route('sop.file', [$sop, 'step', $step->id]) }}" alt="Diagram langkah {{ $step->step_no }}" class="sop-flow-img">
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- ===== Dokumen final (pengesahan pimpinan) ===== --}}
                <div class="sop-box">
                    <h3>Dokumen Final (ditandatangani &amp; distempel)</h3>
                    @if ($sop->hasFinalDocument())
                        <a class="primary-button" href="{{ route('sop.file', [$sop, 'final']) }}" target="_blank" rel="noopener"><x-icon name="arrow-right" :size="15" /> Lihat dokumen final</a>
                    @else
                        <p class="sop-empty">Belum ada dokumen final.</p>
                    @endif

                    @auth
                        <form method="POST" action="{{ route('sop.final', $sop) }}" enctype="multipart/form-data" class="sop-final-form">
                            @csrf
                            <label class="field">
                                <span>Unggah dokumen final (PDF, sudah TTD &amp; stempel pimpinan)</span>
                                <input type="file" name="final_document" accept="application/pdf" required>
                                @error('final_document') <em class="field-error">{{ $message }}</em> @enderror
                            </label>
                            <button type="submit" class="secondary-button">Unggah</button>
                        </form>
                    @endauth
                </div>

                @if ($sop->document)
                    <p class="sop-linked">Tertaut ke dokumen: <a href="{{ route('knowledge-base.domain', $sop->document->category->domain) }}#{{ $sop->document->category->slug }}">{{ $sop->document->title }}</a></p>
                @endif
            </div>
        </div>
    </main>
</x-knowledge-base.layout>
