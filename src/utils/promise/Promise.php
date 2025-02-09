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

use Closure;
use LogicException;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use synopsie\utils\promise\internal\RejectedPromise;
use Throwable;
use function _checkTypehint;
use function is_array;
use function is_object;
use function reject;
use function resolve;

final class Promise implements PromiseInterface {
	/** @var callable */
	private $canceller;
	private $result;
	private array $handlers             = [];
	private int $requiredCancelRequests = 0;

	/**
	 * @throws ReflectionException
	 */
	public function __construct(
		callable $resolver,
		callable $canceller = null
	) {
		$this->canceller = $canceller;
		$cb              = $resolver;
		$resolver        = $canceller = null;
		$this->call($cb);
	}

	/**
	 * @throws ReflectionException
	 */
	public function then(callable $onFulfilled = null, callable $onRejected = null) : PromiseInterface {
		if(null !== $this->result) {
			return $this->result->then($onFulfilled, $onRejected);
		}
		if(null === $this->canceller) {
			return new Promise($this->resolver($onFulfilled, $onRejected));
		}
		$parent = $this;
		++$parent->requiredCancelRequests;

		return new Promise(
			$this->resolver($onFulfilled, $onRejected),
			static function () use (&$parent) {
				--$parent->requiredCancelRequests;
				if($parent->requiredCancelRequests <= 0) {
					$parent->cancel();
				}
				$parent = null;
			}
		);
	}

	public function catch(callable $onRejected) : PromiseInterface {
		return $this->then(null, static function ($reason) use ($onRejected) {
			if (!_checkTypehint($onRejected, $reason)) {
				return new RejectedPromise($reason);
			}

			return $onRejected($reason);
		});
	}

	public function finally(callable $onFulfilledOrRejected) : PromiseInterface {
		return $this->then(static function ($value) use ($onFulfilledOrRejected) {
			return resolve($onFulfilledOrRejected())->then(function () use ($value) {
				return $value;
			});
		}, static function ($reason) use ($onFulfilledOrRejected) {
			return resolve($onFulfilledOrRejected())->then(function () use ($reason) {
				return new RejectedPromise($reason);
			});
		});
	}

	public function cancel() : void {
		$canceller       = $this->canceller;
		$this->canceller = null;

		$parentCanceller = null;

		if (null !== $this->result) {
			// Go up the promise chain and reach the top most promise which is
			// itself not following another promise
			$root = $this->unwrap($this->result);

			// Return if the root promise is already resolved or a
			// FulfilledPromise or RejectedPromise
			if (!$root instanceof self || null !== $root->result) {
				return;
			}

			$root->requiredCancelRequests--;

			if ($root->requiredCancelRequests <= 0) {
				$parentCanceller = [$root, 'cancel'];
			}
		}

		if (null !== $canceller) {
			$this->call($canceller);
		}

		// For BC, we call the parent canceller after our own canceller
		if ($parentCanceller) {
			$parentCanceller();
		}
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

	/**
	 * @deprecated
	 */
	public function wait() : void {
	}

	public function isResolved() : bool {
		return $this->result !== null;
	}

	private function resolver(callable $onFulfilled = null, callable $onRejected = null) : callable {
		return function ($resolve, $reject) use ($onFulfilled, $onRejected) {
			$this->handlers[] = static function (PromiseInterface $promise) use ($onFulfilled, $onRejected, $resolve, $reject) {
				$promise = $promise->then($onFulfilled, $onRejected);

				if ($promise instanceof self && $promise->result === null) {
					$promise->handlers[] = static function (PromiseInterface $promise) use ($resolve, $reject) {
						$promise->then($resolve, $reject);
					};
				} else {
					$promise->then($resolve, $reject);
				}
			};
		};
	}

	private function reject(Throwable $reason) : void {
		if (null !== $this->result) {
			return;
		}

		$this->settle(reject($reason));
	}

	private function settle(PromiseInterface $result) : void {
		$result = $this->unwrap($result);

		if ($result === $this) {
			$result = new RejectedPromise(
				new LogicException('Cannot resolve a promise with itself.')
			);
		}

		if ($result instanceof self) {
			$result->requiredCancelRequests++;
		} else {
			// Unset canceller only when not following a pending promise
			$this->canceller = null;
		}

		$handlers = $this->handlers;

		$this->handlers = [];
		$this->result   = $result;

		foreach ($handlers as $handler) {
			$handler($result);
		}
	}

	private function unwrap($promise) : PromiseInterface {
		while ($promise instanceof self && null !== $promise->result) {
			$promise = $promise->result;
		}

		return $promise;
	}

	/**
	 * @throws ReflectionException
	 */
	private function call(callable $cb) : void {
		// Explicitly overwrite argument with null value. This that this
		// argument does not show up in the stack trace in PHP 7+ only.
		$callback = $cb;
		$cb       = null;

		// Use reflection to inspect number of arguments expected by this callback.
		// We did some careful benchmarking here: Using reflection to avoid unneeded
		// function arguments is actually faster than blindly passing them.
		// Also, this helps unnecessary function arguments in the call stack
		// if the callback creates an Exception (creating garbage cycles).
		if (is_array($callback)) {
			$ref = new ReflectionMethod($callback[0], $callback[1]);
		} elseif (is_object($callback) && !$callback instanceof Closure) {
			$ref = new ReflectionMethod($callback, '__invoke');
		} else {
			$ref = new ReflectionFunction($callback);
		}
		$args = $ref->getNumberOfParameters();

		try {
			if ($args === 0) {
				$callback();
			} else {
				// Keep references to this promise instance for the static resolve/reject functions.
				// By using static callbacks that are not bound to this instance
				// and passing the target promise instance by reference, we can
				// still execute its resolving logic and still clear this
				// reference when settling the promise. This helps
				// garbage cycles if any callback creates an Exception.
				// These assumptions are covered by the tests suite, so if you ever feel like
				// refactoring this, go ahead, any alternative suggestions are welcome!
				$target = &$this;

				$callback(
					static function ($value) use (&$target) {
						if ($target !== null) {
							$target->settle(resolve($value));
							$target = null;
						}
					},
					static function (Throwable $reason) use (&$target) {
						if ($target !== null) {
							$target->reject($reason);
							$target = null;
						}
					}
				);
			}
		} catch (Throwable $e) {
			$target = null;
			$this->reject($e);
		}
	}

}
