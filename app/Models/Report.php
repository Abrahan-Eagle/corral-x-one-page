<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Modelo Report - Reportes y Moderación
 * 
 * Representa los reportes realizados por usuarios sobre contenido inapropiado.
 * Sistema de moderación con revisión por administradores.
 * 
 * Características principales:
 * - Relación polimórfica con múltiples modelos (Product, Profile, Ranch)
 * - Relación N:1 con Profile (reporter y admin)
 * - Sistema de estados y moderación
 * - Tipos de reporte predefinidos
 */
class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'reportable_type',
        'reportable_id',
        'report_type',
        'description',
        'status',
        'admin_id',
        'admin_notes',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * Relación N:1 con Profile (reporter)
     * Un reporte pertenece a un perfil que reporta
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'reporter_id');
    }

    /**
     * Relación N:1 con Profile (admin)
     * Un reporte puede ser revisado por un admin
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'admin_id');
    }

    /**
     * Relación polimórfica
     * Un reporte puede ser sobre cualquier modelo reportable
     */
    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * MÉTODOS DE CONVENIENCIA
     */

    /**
     * Verificar si el reporte está pendiente
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Verificar si el reporte está siendo revisado
     */
    public function isReviewing(): bool
    {
        return $this->status === 'reviewing';
    }

    /**
     * Verificar si el reporte está resuelto
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Verificar si el reporte fue desestimado
     */
    public function isDismissed(): bool
    {
        return $this->status === 'dismissed';
    }

    /**
     * Marcar el reporte como en revisión
     */
    public function markAsReviewing(int $adminId): bool
    {
        $this->status = 'reviewing';
        $this->admin_id = $adminId;
        return $this->save();
    }

    /**
     * Resolver el reporte
     */
    public function resolve(int $adminId, ?string $adminNotes = null): bool
    {
        $this->status = 'resolved';
        $this->admin_id = $adminId;
        $this->admin_notes = $adminNotes;
        $this->resolved_at = now();
        return $this->save();
    }

    /**
     * Desestimar el reporte
     */
    public function dismiss(int $adminId, ?string $adminNotes = null): bool
    {
        $this->status = 'dismissed';
        $this->admin_id = $adminId;
        $this->admin_notes = $adminNotes;
        $this->resolved_at = now();
        return $this->save();
    }

    /**
     * Obtener el tipo de reporte como texto
     */
    public function getReportTypeTextAttribute(): string
    {
        $types = [
            'spam' => 'Spam',
            'inappropriate' => 'Contenido inapropiado',
            'fraud' => 'Fraude',
            'fake_product' => 'Producto falso',
            'harassment' => 'Acoso',
            'other' => 'Otro'
        ];

        return $types[$this->report_type] ?? 'Desconocido';
    }

    /**
     * Obtener el estado como texto
     */
    public function getStatusTextAttribute(): string
    {
        $statuses = [
            'pending' => 'Pendiente',
            'reviewing' => 'En revisión',
            'resolved' => 'Resuelto',
            'dismissed' => 'Desestimado'
        ];

        return $statuses[$this->status] ?? 'Desconocido';
    }

    /**
     * Verificar si el reporte tiene descripción
     */
    public function hasDescription(): bool
    {
        return !empty($this->description);
    }

    /**
     * Obtener la descripción truncada
     */
    public function getTruncatedDescriptionAttribute(int $length = 100): string
    {
        if (!$this->description) {
            return '';
        }

        return strlen($this->description) > $length 
            ? substr($this->description, 0, $length) . '...'
            : $this->description;
    }

    /**
     * Obtener el tiempo transcurrido desde que se creó
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Obtener reportes pendientes
     */
    public static function getPendingReports()
    {
        return self::where('status', 'pending')
                  ->with(['reporter', 'reportable'])
                  ->orderBy('created_at', 'asc')
                  ->get();
    }

    /**
     * Obtener reportes por tipo
     */
    public static function getReportsByType(string $reportType)
    {
        return self::where('report_type', $reportType)
                  ->with(['reporter', 'reportable', 'admin'])
                  ->orderBy('created_at', 'desc')
                  ->get();
    }

    /**
     * Obtener reportes de un perfil
     */
    public static function getProfileReports(int $profileId)
    {
        return self::where('reporter_id', $profileId)
                  ->with(['reportable', 'admin'])
                  ->orderBy('created_at', 'desc')
                  ->get();
    }

    /**
     * Obtener reportes revisados por un admin
     */
    public static function getAdminReports(int $adminId)
    {
        return self::where('admin_id', $adminId)
                  ->with(['reporter', 'reportable'])
                  ->orderBy('resolved_at', 'desc')
                  ->get();
    }

    /**
     * Obtener estadísticas de reportes
     */
    public static function getReportStats(): array
    {
        return [
            'total' => self::count(),
            'pending' => self::where('status', 'pending')->count(),
            'reviewing' => self::where('status', 'reviewing')->count(),
            'resolved' => self::where('status', 'resolved')->count(),
            'dismissed' => self::where('status', 'dismissed')->count(),
            'by_type' => self::selectRaw('report_type, COUNT(*) as count')
                      ->groupBy('report_type')
                      ->pluck('count', 'report_type')
                      ->toArray(),
        ];
    }

    /**
     * SCOPES PARA CONSULTAS FRECUENTES
     */

    /**
     * Scope para reportes pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para reportes en revisión
     */
    public function scopeReviewing($query)
    {
        return $query->where('status', 'reviewing');
    }

    /**
     * Scope para reportes resueltos
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Scope para reportes desestimados
     */
    public function scopeDismissed($query)
    {
        return $query->where('status', 'dismissed');
    }

    /**
     * Scope para reportes por tipo
     */
    public function scopeByType($query, string $reportType)
    {
        return $query->where('report_type', $reportType);
    }

    /**
     * Scope para reportes por reporter
     */
    public function scopeByReporter($query, int $reporterId)
    {
        return $query->where('reporter_id', $reporterId);
    }

    /**
     * Scope para reportes por admin
     */
    public function scopeByAdmin($query, int $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    /**
     * Scope para reportes recientes
     */
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Scope para reportes con descripción
     */
    public function scopeWithDescription($query)
    {
        return $query->whereNotNull('description')
                    ->where('description', '!=', '');
    }
}
