<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class McpRequest extends Model
{
    use HasFactory;

    protected $table = 'mcp_requests';

    protected $fillable = [
        'session_id',
        'request_text',
        'request_type',
        'search_parameters',
        'response_text',
        'found_files',
        'files_count',
        'status',
        'processing_time',
        'error_message',
        'user_ip',
        'user_agent',
    ];

    protected $casts = [
        'search_parameters' => 'array',
        'found_files' => 'array',
        'files_count' => 'integer',
        'processing_time' => 'decimal:3',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    const REQUEST_TYPE_SEARCH = 'search';
    const REQUEST_TYPE_SUMMARY = 'summary';
    const REQUEST_TYPE_LIST = 'list';

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('request_type', $type);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isFailed()
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isProcessing()
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function getFormattedProcessingTimeAttribute()
    {
        return $this->processing_time ? number_format($this->processing_time, 3) . 's' : null;
    }

    public function getSearchTermsAttribute()
    {
        return $this->search_parameters['terms'] ?? [];
    }

    public function getFoundFilesCountAttribute()
    {
        return is_array($this->found_files) ? count($this->found_files) : 0;
    }
}
