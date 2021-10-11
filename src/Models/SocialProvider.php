<?php

namespace MadWeb\SocialAuth\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SocialProvider.
 *
 * @property int $id
 *
 * @param  string  $slug
 * @param  string  $label
 *
 * @property string $label
 * @property string $slug
 * @property array $scopes
 * @property array $parameters
 * @property bool $override_scopes
 * @property bool $stateless
 * @property string $created_at
 * @property string $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $users
 *
 * @method static \Illuminate\Database\Query\Builder|\MadWeb\SocialAuth\Models\SocialProvider whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\MadWeb\SocialAuth\Models\SocialProvider whereLabel($value)
 * @method static \Illuminate\Database\Query\Builder|\MadWeb\SocialAuth\Models\SocialProvider whereSlug($value)
 * @method static \Illuminate\Database\Query\Builder|\MadWeb\SocialAuth\Models\SocialProvider whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\MadWeb\SocialAuth\Models\SocialProvider whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SocialProvider extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'slug',
        'label',
        'scopes',
        'parameters',
        'override_scopes',
        'stateless',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'scopes' => 'array',
        'parameters' => 'array',
    ];

    /**
     * SocialProvider constructor.
     *
     * @param  array  $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('social-auth.table_names.social_providers'));
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Set scopes which will be add to the social auth request.
     */
    public function setScopes(array $scopes, bool $isOverride = false)
    {
        $this->scopes = $scopes;
        $this->override_scopes = $isOverride;

        $this->save();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(
            config('social-auth.models.user'),
            config('social-auth.table_names.user_has_social_provider')
        );
    }
}
