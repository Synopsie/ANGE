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

namespace synopsie\utils\promise\internal;

use InvalidArgumentException;
use synopsie\utils\promise\PromiseInterface;

use Throwable;
use function synopsie\utils\promise\resolve;

final class FulfilledPromise implements PromiseInterface {
	/** @var mixed|null */
	private mixed $value;

	public function __construct($value = null) {
		if($value instanceof PromiseInterface) {
			throw new InvalidArgumentException(
				"You cannot create arkania\\utils\\promise\\internal\\FulfilledPromise with a promise. Use arkania\\utils\\promise\\resolve(\$promiseOrValue) instead."
			);
		}
		$this->value = $value;
	}

	public function then(callable $onFulfilled = null, callable $onRejected = null) : PromiseInterface {
		if (null === $onFulfilled) {
			return $this;
		}

		try {
			return resolve($onFulfilled($this->value));
		} catch (Throwable $exception) {
			return new RejectedPromise($exception);
		}
	}

	public function catch(callable $onRejected) : PromiseInterface {
		return $this;
	}

	/**
	 * @phpstan-param callable(): mixed $onFulfilledOrRejected
	 */
	public function finally(callable $onFulfilledOrRejected) : PromiseInterface {
		return $this->then(function ($value) use ($onFulfilledOrRejected) : PromiseInterface {
			return resolve($onFulfilledOrRejected())->then(function () use ($value) {
				return $value;
			});
		});
	}

	public function cancel() : void {
	}

	/**
	 * @deprecated 3.0.0 Use `catch()` instead
	 * @see self::catch()
	 */
	public function otherwise(callable $onRejected) : PromiseInterface {
		return $this->catch($onRejected);
	}

	/**
	 * @deprecated 3.0.0 Use `finally()` instead
	 * @see self::finally()
	 */
	public function always(callable $onFulfilledOrRejected) : PromiseInterface {
		return $this->finally($onFulfilledOrRejected);
	}

	public function wait() : void {
		// NOOP
	}

	public function isResolved() : bool {
		return true;
	}

}
