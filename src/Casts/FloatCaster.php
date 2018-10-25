<?php namespace Arcanedev\LaravelCastable\Casts;

class FloatCaster extends AbstractCaster
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * @param  mixed  $value
     *
     * @return float
     */
    public function cast($value)
    {
        switch ((string) $value) {
            case 'Infinity':
                return INF;

            case '-Infinity':
                return -INF;

            case 'NaN':
                return NAN;

            default:
                return (float) $value;
        }
    }

    /**
     * @param  float  $value
     *
     * @return string
     */
    public function uncast($value)
    {
        return (string) $value;
    }
}
