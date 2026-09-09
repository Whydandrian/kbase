<?php

namespace App\Models;

use Database\Factories\SopFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Sop extends Model
{
    /** @use HasFactory<SopFactory> */
    use HasFactory;

    public const ITEM_TYPES = [
        'tujuan' => 'Tujuan',
        'ruang_lingkup' => 'Ruang Lingkup',
        'istilah' => 'Istilah dan Definisi',
        'dasar_hukum' => 'Dasar Hukum',
        'kualifikasi' => 'Kualifikasi Pelaksana',
        'keterkaitan' => 'Keterkaitan',
        'peralatan' => 'Peralatan/Perlengkapan',
        'peringatan' => 'Peringatan',
        'pencatatan' => 'Pencatatan dan Pendataan',
    ];

    public const APPROVAL_ROLES = ['penyusun', 'pemeriksa', 'pengesahan'];

    protected $fillable = [
        'document_id',
        'nomor_sop',
        'nama_sop',
        'unit_pembuat',
        'kementerian',
        'institusi',
        'tgl_pembuatan',
        'tgl_revisi',
        'tgl_efektif',
        'final_document_path',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tgl_pembuatan' => 'date',
            'tgl_revisi' => 'date',
            'tgl_efektif' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Document, $this>
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<SopApproval, $this>
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(SopApproval::class);
    }

    /**
     * @return HasMany<SopItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(SopItem::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<SopFlowStep, $this>
     */
    public function flowSteps(): HasMany
    {
        return $this->hasMany(SopFlowStep::class)->orderBy('sort_order')->orderBy('step_no');
    }

    /**
     * Items of a given type.
     *
     * @return Collection<int, SopItem>
     */
    public function itemsOfType(string $type): Collection
    {
        return $this->items->where('type', $type)->values();
    }

    public function approvalFor(string $role): ?SopApproval
    {
        return $this->approvals->firstWhere('role', $role);
    }

    public function hasFinalDocument(): bool
    {
        return (bool) $this->final_document_path;
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
