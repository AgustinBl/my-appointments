<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkDay extends Model
{
    protected $fillable = [

	    'day',
	    'active',

	    'morning_start',
	    'morning_end',

	    'afternoon_start',
	    'afternoon_end',

	    'user_id'
    ];

    /** 0 = lunes, igual que la columna `day`. */
    const NAMES = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
    const SHORT = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * «jueves» · «lunes y jueves» · «lunes, miércoles y viernes»
     */
    public static function daysSentence(array $days, $short = false)
    {
        $source = $short ? self::SHORT : self::NAMES;
        $names = [];

        foreach ($days as $day) {
            if (isset($source[$day])) {
                $names[] = $source[$day];
            }
        }

        if (count($names) <= 1) {
            return implode('', $names);
        }

        $last = array_pop($names);

        return implode(', ', $names).' y '.$last;
    }
}
