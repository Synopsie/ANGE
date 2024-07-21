<?php

/*
 *  ____   __   __  _   _    ___    ____    ____    ___   _____
 * / ___|  \ \ / / | \ | |  / _ \  |  _ \  / ___|  |_ _| | ____|
 * \___ \   \ V /  |  \| | | | | | | |_) | \___ \   | |  |  _|
 *  ___) |   | |   | |\  | | |_| | |  __/   ___) |  | |  | |___
 * |____/    |_|   |_| \_|  \___/  |_|     |____/  |___| |_____|
 *
 * @author Julien
 * @link https://arkaniastudios.com
 * @version 0.0.1-alpha
 *
 */

declare(strict_types=1);

namespace synopsie\utils\time;

use DateTime;
use function date;
use function date_default_timezone_set;
use function time;

final class Date {
	private string $second;
	private string $minute;
	private string $hour;
	private string $day;
	private string $month;
	private string $year;
	private string $dayNumber;
	private string $monthNumber;

	private bool $viewSecond    = false;
	private bool $viewMinute    = true;
	private bool $viewHour      = true;
	private bool $viewDay       = true;
	private bool $viewDayNumber = true;
	private bool $viewMonth     = true;
	private bool $viewYear      = true;

	public static function create(null|DateTime|int $date = null) : self {
		return new self($date);
	}

	public function __construct(
		null|DateTime|int $date = null
	) {
		date_default_timezone_set('Europe/Paris');
		if ($date instanceof DateTime) {
			$time = $date->getTimestamp();
		} else {
			$time = $date ?? time();
		}
		$days              = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
		$months            = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
		$this->second      = date('s', $time);
		$this->minute      = date('i', $time);
		$this->hour        = date('H', $time);
		$this->day         = $days[date('w', $time)];
		$this->month       = $months[date('n', $time) - 1];
		$this->year        = date('Y', $time);
		$this->dayNumber   = date('d', $time);
		$this->monthNumber = date('m', $time);
	}

	public function toShortFormat() : string {
		return $this->dayNumber . '/' . $this->monthNumber . '/' . $this->year;
	}

	public function __toString() : string {
		$date = '';
		if($this->viewSecond && $this->viewMinute && $this->viewHour && $this->viewDay && $this->viewDayNumber && $this->viewMonth && $this->viewYear) {
			$date = $this->day . ' ' . $this->dayNumber . ' ' . $this->month . ' ' . $this->year . ' à ' . $this->hour . 'H' . $this->minute . '.' . $this->second;
		} elseif($this->viewSecond && $this->viewMinute && $this->viewHour && $this->viewDay && $this->viewDayNumber && $this->viewMonth) {
			$date = $this->day . ' ' . $this->dayNumber . ' ' . $this->month . ' à ' . $this->hour . 'H' . $this->minute . '.' . $this->second;
		} elseif($this->viewSecond && $this->viewMinute && $this->viewHour && $this->viewDay && $this->viewDayNumber) {
			$date = $this->day . ' ' . $this->dayNumber . ' ' . $this->month . ' à ' . $this->hour . 'H' . $this->minute;
		} elseif($this->viewSecond && $this->viewMinute && $this->viewHour && $this->viewDay) {
			$date = $this->day . ' ' . $this->dayNumber . ' ' . $this->month . ' à ' . $this->hour . 'H' . $this->minute;
		} elseif($this->viewSecond && $this->viewMinute && $this->viewHour) {
			$date = $this->day . ' ' . $this->dayNumber . ' ' . $this->month . ' à ' . $this->hour . 'H' . $this->minute;
		} elseif($this->viewSecond && $this->viewMinute) {
			$date = $this->day . ' ' . $this->dayNumber . ' ' . $this->month . ' à ' . $this->hour . 'H' . $this->minute;
		} elseif($this->viewSecond) {
			$date = $this->day . ' ' . $this->dayNumber . ' ' . $this->month . ' à ' . $this->hour . 'H' . $this->minute;
		} elseif($this->viewMinute) {
			$date = $this->day . ' ' . $this->dayNumber . ' ' . $this->month . ' à ' . $this->hour . 'H' . $this->minute;
		} elseif($this->viewHour) {
			$date = $this->day . ' ' . $this->dayNumber . ' ' . $this->month . ' à ' . $this->hour . 'H';
		} elseif($this->viewDay) {
			$date = $this->day . ' ' . $this->dayNumber . ' ' . $this->month;
		} elseif($this->viewDayNumber) {
			$date = $this->day . ' ' . $this->dayNumber;
		} elseif($this->viewMonth) {
			$date = $this->day . ' ' . $this->month;
		} elseif($this->viewYear) {
			$date = $this->day . ' ' . $this->year;
		}
		return $date;
	}

	public function viewSecond() : self {
		$this->viewSecond = true;
		return $this;
	}

	public function viewMinute() : self {
		$this->viewMinute = true;
		return $this;
	}

	public function viewHour() : self {
		$this->viewHour = true;
		return $this;
	}

	public function viewDay() : self {
		$this->viewDay = true;
		return $this;
	}

	public function viewDayNumber() : self {
		$this->viewDayNumber = true;
		return $this;
	}

	public function viewMonth() : self {
		$this->viewMonth = true;
		return $this;
	}

	public function viewYear() : self {
		$this->viewYear = true;
		return $this;
	}

	public function hideSecond() : self {
		$this->viewSecond = false;
		return $this;
	}

	public function hideMinute() : self {
		$this->viewMinute = false;
		return $this;
	}

	public function hideHour() : self {
		$this->viewHour = false;
		return $this;
	}

	public function hideDay() : self {
		$this->viewDay = false;
		return $this;
	}

	public function hideDayNumber() : self {
		$this->viewDayNumber = false;
		return $this;
	}

	public function hideMonth() : self {
		$this->viewMonth = false;
		return $this;
	}

	public function hideYear() : self {
		$this->viewYear = false;
		return $this;
	}

}
