<?php namespace Arcanedev\LaravelCastable\Casts;

class DecimalCaster extends AbstractCaster
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    protected $decimalSeparator   = '.';

    protected $thousandsSeparator = '';

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * @param  mixed $value
     *
     * @return mixed
     */
    public function cast($value)
    {
        return number_format($value, $this->getFormat(), $this->decimalSeparator, $this->thousandsSeparator);
    }

    /**
     * Get the format.
     *
     * @return int|string
     */
    protected function getFormat()
    {
        return $this->format ?? 0;
    }
}
