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

use ReflectionException;
use synopsie\utils\promise\PromiseInterface;

use Throwable;
use function synopsie\utils\promise\_checkTypehint;
use function synopsie\utils\promise\resolve;

final class RejectedPromise implements PromiseInterface {
	private Throwable $reason;

	public function __construct(
		Throwable $reason
	) {
		$this->reason = $reason;
	}

	public function then(callable $onFulfilled = null, callable $onRejected = null) : PromiseInterface {
		if (null === $onRejected) {
			return $this;
		}

		try {
			return resolve($onRejected($this->reason));
		} catch (Throwable $exception) {
			return new RejectedPromise($exception);
		}
	}

	/**
	 * @phpstan-param callable(Throwable): mixed $onRejected
	 * @throws ReflectionException
	 */
	public function catch(callable $onRejected) : PromiseInterface {
		if (!_checkTypehint($onRejected, $this->reason)) {
			return $this;
		}

		return $this->then(null, $onRejected);
	}

	/**
	 * @phpstan-param callable(): mixed $onFulfilledOrRejected
	 */
	public function finally(callable $onFulfilledOrRejected) : PromiseInterface {
		return $this->then(null, function (Throwable $reason) use ($onFulfilledOrRejected) : PromiseInterface {
			return resolve($onFulfilledOrRejected())->then(function () use ($reason) : PromiseInterface {
				return new RejectedPromise($reason);
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
	 * @deprecated 3.0.0 Use `always()` instead
	 * @see self::always()
	 */
	public function always(callable $onFulfilledOrRejected) : PromiseInterface {
		return $this->finally($onFulfilledOrRejected);
	}

	public function wait() : void {
	}

	public function isResolved() : bool {
		return true;
	}

}
