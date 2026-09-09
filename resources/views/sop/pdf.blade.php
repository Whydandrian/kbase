@php
    use Illuminate\Support\Facades\Storage;
    $roles = \App\Models\Sop::APPROVAL_ROLES;
    $sigPath = fn ($p) => $p && Storage::disk('local')->exists($p) ? Storage::disk('local')->path($p) : null;
@endphp
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
    * { font-family: DejaVu Sans, sans-serif; }
    body { font-size: 10px; color: #1a2436; margin: 0; }
    h1 { font-size: 15px; text-align: center; margin: 0 0 2px; }
    h2 { font-size: 12px; margin: 14px 0 6px; }
    h3 { font-size: 11px; margin: 10px 0 4px; }
    table { width: 100%; border-collapse: collapse; }
    .identity td, .identity th { border: 1px solid #333; padding: 4px 6px; vertical-align: top; }
    .identity th { background: #eef1f5; text-align: left; width: 90px; }
    .org { text-align: center; width: 200px; font-size: 9px; }
    .approvals td, .approvals th { border: 1px solid #333; padding: 4px 6px; text-align: center; }
    .approvals th { background: #eef1f5; }
    .sign-img { height: 44px; }
    .cols { width: 100%; }
    .cols td { width: 50%; border: 1px solid #333; padding: 6px; vertical-align: top; }
    ul { margin: 0; padding-left: 16px; }
    .flow td, .flow th { border: 1px solid #333; padding: 4px; font-size: 9px; }
    .flow th { background: #eef1f5; }
    .flow-img { max-height: 60px; }
    .pre { white-space: pre-line; }
    .muted { color: #888; }
</style>
</head>
<body>
    <h1>STANDAR OPERASIONAL PROSEDUR</h1>
    <h1>{{ strtoupper($sop->nama_sop) }}</h1>

    <h2>Identitas SOP</h2>
    <table class="identity">
        <tr>
            <td class="org" rowspan="4">
                <strong>{{ $sop->kementerian }}</strong><br>{{ $sop->institusi }}<br>{{ $sop->unit_pembuat }}
            </td>
            <th>Nomor SOP</th><td>{{ $sop->nomor_sop }}</td>
        </tr>
        <tr><th>Tgl. Pembuatan</th><td>{{ $sop->tgl_pembuatan?->format('d F Y') }}</td></tr>
        <tr><th>Tgl. Revisi</th><td>{{ $sop->tgl_revisi?->format('d F Y') }}</td></tr>
        <tr><th>Tgl. Efektif</th><td>{{ $sop->tgl_efektif?->format('d F Y') }}</td></tr>
        <tr><th>Nama SOP</th><td colspan="2">{{ $sop->nama_sop }}</td></tr>
    </table>

    <table class="approvals" style="margin-top:6px;">
        <tr><th></th>@foreach ($roles as $r)<th>{{ ucfirst($r) }}</th>@endforeach</tr>
        <tr><td>Tanggal</td>@foreach ($roles as $r)<td>{{ $sop->approvalFor($r)?->tanggal?->format('d M Y') }}</td>@endforeach</tr>
        <tr><td>Nama</td>@foreach ($roles as $r)<td>{{ $sop->approvalFor($r)?->nama }}</td>@endforeach</tr>
        <tr><td>Jabatan</td>@foreach ($roles as $r)<td>{{ $sop->approvalFor($r)?->jabatan }}</td>@endforeach</tr>
        <tr><td>Tanda tangan</td>
            @foreach ($roles as $r)
                @php($ap = $sop->approvalFor($r))
                <td>
                    @if ($ap && $ap->hasSignature() && $sigPath($ap->signature_path))
                        <img class="sign-img" src="{{ $sigPath($ap->signature_path) }}">
                    @elseif ($r === 'pengesahan' && $sop->hasFinalDocument())
                        <span class="muted">TTD &amp; stempel (dok. final)</span>
                    @endif
                </td>
            @endforeach
        </tr>
    </table>

    <h2>Keterangan</h2>
    <table class="cols">
        <tr>
            <td>
                <h3>Dasar Hukum</h3>
                <ul>@forelse ($sop->itemsOfType('dasar_hukum') as $i)<li>{{ $i->content }}</li>@empty<li class="muted">—</li>@endforelse</ul>
            </td>
            <td>
                <h3>Kualifikasi Pelaksana</h3>
                <ul>@forelse ($sop->itemsOfType('kualifikasi') as $i)<li>{{ $i->content }}</li>@empty<li class="muted">—</li>@endforelse</ul>
            </td>
        </tr>
        <tr>
            <td>
                <h3>Keterkaitan</h3>
                <ul>@forelse ($sop->itemsOfType('keterkaitan') as $i)<li>{{ $i->content }}</li>@empty<li class="muted">—</li>@endforelse</ul>
            </td>
            <td>
                <h3>Peralatan/Perlengkapan</h3>
                <ul>@forelse ($sop->itemsOfType('peralatan') as $i)<li>{{ $i->content }}</li>@empty<li class="muted">—</li>@endforelse</ul>
            </td>
        </tr>
        <tr>
            <td>
                <h3>Peringatan</h3>
                <ul>@forelse ($sop->itemsOfType('peringatan') as $i)<li>{{ $i->content }}</li>@empty<li class="muted">—</li>@endforelse</ul>
            </td>
            <td>
                <h3>Pencatatan dan Pendataan</h3>
                <ul>@forelse ($sop->itemsOfType('pencatatan') as $i)<li>{{ $i->content }}</li>@empty<li class="muted">—</li>@endforelse</ul>
            </td>
        </tr>
    </table>

    @foreach (['tujuan' => 'Tujuan', 'ruang_lingkup' => 'Ruang Lingkup', 'istilah' => 'Istilah dan Definisi'] as $type => $label)
        @if ($sop->itemsOfType($type)->isNotEmpty())
            <h2>{{ $label }}</h2>
            <ul>@foreach ($sop->itemsOfType($type) as $i)<li>{{ $i->content }}</li>@endforeach</ul>
        @endif
    @endforeach

    @if ($sop->flowSteps->isNotEmpty())
        <h2>Flowchart</h2>
        <table class="flow">
            <tr><th>No</th><th>Kegiatan</th><th>Pelaksana</th><th>Kelengkapan</th><th>Waktu</th><th>Output</th><th>Diagram</th></tr>
            @foreach ($sop->flowSteps as $s)
                <tr>
                    <td>{{ $s->step_no }}</td>
                    <td>{{ $s->kegiatan }}</td>
                    <td>{{ $s->pelaksana }}</td>
                    <td>{{ $s->kelengkapan }}</td>
                    <td>{{ $s->waktu }}</td>
                    <td>{{ $s->output }}</td>
                    <td>@if ($s->hasImage() && $sigPath($s->image_path))<img class="flow-img" src="{{ $sigPath($s->image_path) }}">@endif</td>
                </tr>
            @endforeach
        </table>
    @endif
</body>
</html>
