<?php

namespace ProcessMaker\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rule;
use ProcessMaker\Traits\HideSystemResources;
use ProcessMaker\Traits\SerializeToIso8601;

/**
 * Represents a business datasource category definition.
 *
 * @property string $id
 * @property string $name
 * @property string $status
 * @property Carbon $updated_at
 * @property Carbon $created_at
 *
 * @OA\Schema(
 *   schema="DatasourceCategoryEditable",
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="status", type="string", enum={"ACTIVE", "INACTIVE"}),
 * ),
 * @OA\Schema(
 *   schema="DatasourceCategory",
 *   allOf={@OA\Schema(ref="#/components/schemas/DatasourceCategoryEditable")},
 *   @OA\Property(property="id", type="string", format="id"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time"),
 * )
 */
class DataSourceCategory extends Model
{
    use SerializeToIso8601;
    use HideSystemResources;

    protected $connection = 'processmaker';

    protected $fillable = [
        'name',
        'status',
        'is_system'
    ];

    public static function rules($existing = null)
    {
        $unique = Rule::unique('data_source_categories')->ignore($existing);

        return [
            'name' => ['required', 'string', 'max:100', $unique],
            'status' => 'required|string|in:ACTIVE,INACTIVE'
        ];
    }

    /**
     * Get screens
     *
     * @return HasMany
     */
    public function datasources()
    {
        return $this->hasMany(DataSource::class);
    }
}
