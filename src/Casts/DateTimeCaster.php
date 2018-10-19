<?php namespace Arcanedev\LaravelCastable\Casts;

use DateTimeInterface;
use Illuminate\Support\Carbon;

class DateTimeCaster extends AbstractCaster
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * @param  mixed  $value
     *
     * @return \Illuminate\Support\Carbon
     */
    public function cast($value)
    {
        return static::asDateTime($value);
    }

    /**
     * @param  \Illuminate\Support\Carbon  $value
     *
     * @return string
     */
    public function uncast($value)
    {
        return $value->format(static::dateFormat());
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Return a timestamp as DateTime object.
     *
     * @param  mixed   $value
     *
     * @return \Illuminate\Support\Carbon
     */
    protected static function asDateTime($value)
    {
        // If this value is already a Carbon instance, we shall just return it as is.
        // This prevents us having to re-instantiate a Carbon instance when we know
        // it already is one, which wouldn't be fulfilled by the DateTime check.
        if ($value instanceof Carbon) {
            return $value;
        }

        // If the value is already a DateTime instance, we will just skip the rest of
        // these checks since they will be a waste of time, and hinder performance
        // when checking the field. We will just return the DateTime right away.
        if ($value instanceof DateTimeInterface) {
            return new Carbon(
                $value->format('Y-m-d H:i:s.u'), $value->getTimezone()
            );
        }

        // If this value is an integer, we will assume it is a UNIX timestamp's value
        // and format a Carbon object from this timestamp. This allows flexibility
        // when defining your date fields as they might be UNIX timestamps here.
        if (is_numeric($value)) {
            return Carbon::createFromTimestamp($value);
        }

        // If the value is in simply year, month, day format, we will instantiate the
        // Carbon instances from that format. Again, this provides for simple date
        // fields on the database, while still supporting Carbonized conversion.
        if (static::isStandardDateFormat($value)) {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        }

        // Finally, we will just assume this date is in the format used by default on
        // the database connection and use that format to create the Carbon object
        // that is returned back out to the developers after we convert it here.
        return Carbon::createFromFormat(
            str_replace('.v', '.u', static::dateFormat()), $value
        );
    }

    /**
     * Get the date format.
     *
     * @return string
     */
    public static function dateFormat()
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * Determine if the given value is a standard date format.
     *
     * @param  string  $value
     *
     * @return bool
     */
    protected static function isStandardDateFormat($value)
    {
        return preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value);
    }
}
