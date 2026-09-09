@php($isEdit = $sop->exists)
<x-knowledge-base.layout :title="($isEdit ? 'Edit' : 'Buat') . ' SOP — IT Knowledge Base'">
    <main class="page">
        <div class="page-inner">
            <div class="breadcrumb">
                <a href="{{ route('knowledge-base') }}">Knowledge base</a>
                <x-icon name="chevron-right" :size="14" />
                <a href="{{ route('sop.index') }}">SOP</a>
                <x-icon name="chevron-right" :size="14" />
                <strong>{{ $isEdit ? 'Edit' : 'Buat' }} SOP</strong>
            </div>

            <h1>{{ $isEdit ? 'Edit' : 'Buat' }} SOP</h1>

            @if ($errors->any())
                <div class="alert alert-error"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif

            <form method="POST" action="{{ $isEdit ? route('sop.update', $sop) : route('sop.store') }}" class="sop-form" data-sop-form>
                @csrf
                @if ($isEdit) @method('PUT') @endif

                {{-- Identitas --}}
                <fieldset class="sop-fieldset">
                    <legend>Identitas SOP</legend>
                    <div class="form-grid">
                        <label class="field"><span>Nomor SOP</span><input type="text" name="nomor_sop" value="{{ old('nomor_sop', $sop->nomor_sop) }}" required></label>
                        <label class="field"><span>Nama SOP</span><input type="text" name="nama_sop" value="{{ old('nama_sop', $sop->nama_sop) }}" required></label>
                        <label class="field"><span>Kementerian</span><input type="text" name="kementerian" value="{{ old('kementerian', $sop->kementerian) }}"></label>
                        <label class="field"><span>Institusi</span><input type="text" name="institusi" value="{{ old('institusi', $sop->institusi) }}"></label>
                        <label class="field"><span>Unit Pembuat</span><input type="text" name="unit_pembuat" value="{{ old('unit_pembuat', $sop->unit_pembuat) }}"></label>
                        <label class="field"><span>Tgl. Pembuatan</span><input type="date" name="tgl_pembuatan" value="{{ old('tgl_pembuatan', $sop->tgl_pembuatan?->format('Y-m-d')) }}"></label>
                        <label class="field"><span>Tgl. Revisi</span><input type="date" name="tgl_revisi" value="{{ old('tgl_revisi', $sop->tgl_revisi?->format('Y-m-d')) }}"></label>
                        <label class="field"><span>Tgl. Efektif</span><input type="date" name="tgl_efektif" value="{{ old('tgl_efektif', $sop->tgl_efektif?->format('Y-m-d')) }}"></label>
                        <label class="field field-wide"><span>Tautkan ke dokumen (opsional)</span>
                            <select name="document_id">
                                <option value="">— Tidak ditautkan —</option>
                                @foreach ($documents as $doc)
                                    <option value="{{ $doc->id }}" @selected((int) old('document_id', $sop->document_id) === $doc->id)>{{ $doc->title }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </fieldset>

                {{-- Penyusun / Pemeriksa / Pengesahan --}}
                <fieldset class="sop-fieldset">
                    <legend>Penyusun, Pemeriksa, Pengesahan</legend>
                    <p class="form-hint">Tanda tangan penyusun &amp; pemeriksa dibubuhkan lewat tombol "Setujui" di halaman detail. Pengesahan pimpinan lewat unggah dokumen final.</p>
                    @foreach (\App\Models\Sop::APPROVAL_ROLES as $role)
                        @php($ap = $sop->approvalFor($role))
                        <div class="form-grid sop-approval-row">
                            <div class="field"><span>{{ ucfirst($role) }} — Nama</span><input type="text" name="approvals[{{ $role }}][nama]" value="{{ old("approvals.$role.nama", $ap?->nama) }}"></div>
                            <div class="field"><span>Jabatan</span><input type="text" name="approvals[{{ $role }}][jabatan]" value="{{ old("approvals.$role.jabatan", $ap?->jabatan) }}"></div>
                            <div class="field"><span>Tanggal</span><input type="date" name="approvals[{{ $role }}][tanggal]" value="{{ old("approvals.$role.tanggal", $ap?->tanggal?->format('Y-m-d')) }}"></div>
                        </div>
                    @endforeach
                </fieldset>

                {{-- Item grup berulang (termasuk tujuan, ruang lingkup, istilah) --}}
                @foreach (\App\Models\Sop::ITEM_TYPES as $type => $label)
                    <fieldset class="sop-fieldset">
                        <legend>{{ $label }}</legend>
                        <div class="repeat-list" data-repeat="item" data-type="{{ $type }}">
                            @php($rows = old("items_$type", $sop->itemsOfType($type)->map(fn ($i) => ['content' => $i->content, 'url' => $i->url])->all()))
                            @forelse ($rows as $row)
                                <div class="repeat-row">
                                    <input type="hidden" name="items[{{ $type }}_{{ $loop->index }}][type]" value="{{ $type }}">
                                    <input type="text" name="items[{{ $type }}_{{ $loop->index }}][content]" value="{{ is_array($row) ? ($row['content'] ?? '') : $row }}" placeholder="Isi {{ Str::lower($label) }}">
                                    <input type="url" name="items[{{ $type }}_{{ $loop->index }}][url]" value="{{ is_array($row) ? ($row['url'] ?? '') : '' }}" placeholder="URL (opsional)">
                                    <button type="button" class="icon-button icon-danger" data-repeat-remove><x-icon name="x" :size="15" /></button>
                                </div>
                            @empty
                            @endforelse
                        </div>
                        <button type="button" class="secondary-button" data-repeat-add="item" data-type="{{ $type }}"><x-icon name="file-text" :size="14" /> Tambah {{ $label }}</button>
                    </fieldset>
                @endforeach

                {{-- Flowchart steps --}}
                <fieldset class="sop-fieldset">
                    <legend>Flowchart (langkah)</legend>
                    <div class="repeat-list" data-repeat="step">
                        @foreach ($sop->flowSteps as $step)
                            <div class="repeat-row repeat-step">
                                <input type="text" name="steps[{{ $loop->index }}][kegiatan]" value="{{ $step->kegiatan }}" placeholder="Kegiatan" required>
                                <input type="text" name="steps[{{ $loop->index }}][pelaksana]" value="{{ $step->pelaksana }}" placeholder="Pelaksana">
                                <input type="text" name="steps[{{ $loop->index }}][kelengkapan]" value="{{ $step->kelengkapan }}" placeholder="Kelengkapan">
                                <input type="text" name="steps[{{ $loop->index }}][waktu]" value="{{ $step->waktu }}" placeholder="Waktu">
                                <input type="text" name="steps[{{ $loop->index }}][output]" value="{{ $step->output }}" placeholder="Output">
                                <label class="inline-check"><input type="checkbox" name="steps[{{ $loop->index }}][is_decision]" value="1" @checked($step->is_decision)> Keputusan</label>
                                <input type="file" name="steps[{{ $loop->index }}][image]" accept="image/*">
                                <button type="button" class="icon-button icon-danger" data-repeat-remove><x-icon name="x" :size="15" /></button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="secondary-button" data-repeat-add="step"><x-icon name="layers" :size="14" /> Tambah langkah</button>
                </fieldset>

                <div class="form-actions">
                    <button type="submit" class="primary-button">Simpan SOP <x-icon name="arrow-right" :size="16" /></button>
                    <a href="{{ $isEdit ? route('sop.show', $sop) : route('sop.index') }}" class="secondary-button">Batal</a>
                </div>
            </form>
        </div>
    </main>

    {{-- Row templates for the repeatable sections --}}
    <template id="tpl-item-row">
        <div class="repeat-row">
            <input type="hidden" data-name="items[__KEY__][type]" value="__TYPE__">
            <input type="text" data-name="items[__KEY__][content]" placeholder="Isi item">
            <input type="url" data-name="items[__KEY__][url]" placeholder="URL (opsional)">
            <button type="button" class="icon-button icon-danger" data-repeat-remove>&times;</button>
        </div>
    </template>
    <template id="tpl-step-row">
        <div class="repeat-row repeat-step">
            <input type="text" data-name="steps[__KEY__][kegiatan]" placeholder="Kegiatan" required>
            <input type="text" data-name="steps[__KEY__][pelaksana]" placeholder="Pelaksana">
            <input type="text" data-name="steps[__KEY__][kelengkapan]" placeholder="Kelengkapan">
            <input type="text" data-name="steps[__KEY__][waktu]" placeholder="Waktu">
            <input type="text" data-name="steps[__KEY__][output]" placeholder="Output">
            <label class="inline-check"><input type="checkbox" data-name="steps[__KEY__][is_decision]" value="1"> Keputusan</label>
            <input type="file" data-name="steps[__KEY__][image]" accept="image/*">
            <button type="button" class="icon-button icon-danger" data-repeat-remove>&times;</button>
        </div>
    </template>
</x-knowledge-base.layout>
