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

namespace synopsie\utils\promise;

use ReflectionException;
use Throwable;

/**
 * @template T
 */
class Deferred {
	/** @var PromiseInterface<T> */
	private PromiseInterface $promise;

	/** @var callable(T):void */
	private $resolveCallback;

	/** @var callable(Throwable):void */
	private $rejectCallback;

	/**
	 * @param (callable(callable(T):void,callable(Throwable):void):void)|null $canceller
	 * @throws ReflectionException
	 */
	public function __construct(callable $canceller = null) {
		$this->promise = new Promise(function ($resolve, $reject) : void {
			$this->resolveCallback = $resolve;
			$this->rejectCallback  = $reject;
		}, $canceller);
	}

	/**
	 * @return PromiseInterface<T>
	 */
	public function promise() : PromiseInterface {
		return $this->promise;
	}

	/**
	 * @param T $value
	 */
	public function resolve($value) : void {
		($this->resolveCallback)($value);
	}

	public function reject(Throwable $reason) : void {
		($this->rejectCallback)($reason);
	}

}
