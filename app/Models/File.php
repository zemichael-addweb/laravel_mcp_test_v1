<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'files';

    protected $fillable = [
        'name',
        'type',
        'directory',
        'file_path',
        'size',
        'url',
        'download_count',
        'description',
        'date',
        'created_by',
        'updated_by',
        'index_status',
        'content',
        'metadata',
        'page_count',
        'status',
        'processed_at',
        'audio',
        'box',
        '4_digit_date',
        '6_digit_date',
        '8_digit_date',
        'acc_lecture_sets',
        'alternate_title_per_the_transcript',
        'date_of_authorship',
        'file_number',
        'filter_1',
        'filter_2',
        'filter_3',
        'filter_4',
        'filter_5',
        'filter_6',
        'filter_a',
        'form',
        'issue_data_a',
        'issue_data_b',
        'issue_data_c',
        'issue_data_d',
        'issue_data_e',
        'issue_data_f',
        'lecture_code',
        'lecture_data_additional',
        'lecture_series_original_names',
        'lecture_series_original_names_aka',
        'lecture_series_original_names_with_dates',
        'lecture_set_cassette',
        'lecture_set_cd',
        'long_date',
        'master_filter',
        'mimeo_titles_not_in_tech_vols',
        'note',
        'revised_date',
        'section',
        'series_1_a_b',
        'series_2_c_s',
        'series_3_clear_d',
        'series_4_est_d',
        'series_5_executive_field',
        'series_6_finance_l',
        'series_7_m_pab',
        'series_8_personnel_personnel_management',
        'series_9_personnel_programming_t',
        'series_10_w',
        'source',
        'title',
        'additional_data',
        'address',
        'box_number',
        'description_per_the_periodical',
        'digitized',
        'file_label',
        'file_macro',
        'file_hash',
        'further_description_per_the_periodical',
        'periodicals_id',
        'interview_with',
        'lrh_article',
        'periodicals_notes',
        'organization',
        'reports',
        'tech_popular',
        'title_number',
        'content_sample',
        'folder',
        'silver_files_id',
        'other_particles_in_folder',
        'tab',
    ];

    protected $casts = [
        'metadata' => 'array',
        'date' => 'date',
        'long_date' => 'datetime',
        'processed_at' => 'datetime',
        'download_count' => 'integer',
        'page_count' => 'integer',
        'periodicals_id' => 'integer',
        'silver_files_id' => 'integer',
        'index_status' => 'integer',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'processed_at',
        'date',
        'long_date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeSearchContent($query, $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('content', 'like', '%' . $searchTerm . '%')
                ->orWhere('title', 'like', '%' . $searchTerm . '%')
                ->orWhere('description', 'like', '%' . $searchTerm . '%')
                ->orWhere('name', 'like', '%' . $searchTerm . '%')
                ->orWhere('content_sample', 'like', '%' . $searchTerm . '%')
                ->orWhere('note', 'like', '%' . $searchTerm . '%');
        });
    }

    public function scopeBySource($query, $source)
    {
        return $query->where('source', 'like', '%' . $source . '%');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', 'like', '%' . $type . '%');
    }

    public function scopeIndexed($query)
    {
        return $query->where('index_status', 3);
    }

    public function getFormattedSizeAttribute()
    {
        return $this->size;
    }

    public function isIndexed()
    {
        return $this->index_status === 3;
    }
}
