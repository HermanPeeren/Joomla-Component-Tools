<?php
/**
 * @package    EventSchedule
 * @subpackage com_eventschedule
 * @version    1.0.0
 *
 * @copyright  Herman Peeren, Yepr
 * @license    GPL vs3+
 */

namespace Yepr\Component\EventSchedule\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;

/**
 * Eventschedule component helper
 */
abstract class EventscheduleHelper
{

    public static function createHourlyTimeSlots($start, $end)
    {
        $startTime = new \DateTime($start);
        $endTime = new \DateTime($end);
        $timeSlots = [];

        while ($startTime <= $endTime) {
            $timeSlots[] = $startTime->format('H:i');
            $startTime->modify('+1 hour');
        }

        return $timeSlots;
    }

    public static function timeDifferenceInMinutes($startTime, $endTime)
    {
        $start = new \DateTime($startTime);
        $end = new \DateTime($endTime);
        $interval = $start->diff($end);

        return ($interval->h * 60) + $interval->i;
    }

    public static function calculateEventTop($eventStart, $startOfDay, $slotHeight)
    {
        $startOfDayTime = new \DateTime($startOfDay);
        $eventStartTime = new \DateTime($eventStart);
        $minutesFromStart = self::timeDifferenceInMinutes($startOfDayTime->format('H:i'), $eventStartTime->format('H:i'));

        return ($minutesFromStart / 60) * $slotHeight;
    }

    public static function calculateEventHeight($eventStart, $eventEnd, $slotHeight)
    {
        $eventDurationMinutes = self::timeDifferenceInMinutes($eventStart, $eventEnd);

        return ($eventDurationMinutes / 60) * $slotHeight;
    }
}
