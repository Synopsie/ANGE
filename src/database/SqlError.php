<?php

declare(strict_types=1);

namespace synopsie\database;

use Closure;
use Exception;
use ReflectionClass;
use ReflectionFunction;
use RuntimeException;
use function array_walk_recursive;
use function get_class;
use function get_resource_type;
use function is_object;
use function is_resource;
use function json_encode;
use function sprintf;

class SqlError extends RuntimeException {
	/**
	 * Returned by {@link SqlError::getStage() getStage()}, indicating that an error occurred while connecting to the database
	 */
	public const STAGE_CONNECT = "CONNECT";
	/**
	 * Returned by {@link SqlError::getStage() getStage()}, indicating that an error occurred while preparing the query
	 */
	public const STAGE_PREPARE = "PREPARE";
	/**
	 * Returned by {@link SqlError::getStage() getStage()}, indicating that an error occurred while the SQL backend is executing the query
	 */
	public const STAGE_EXECUTE = "EXECUTION";
	/**
	 * Returned by {@link SqlError::getStage() getStage()}, indicating that an error occurred while handling the response of the query
	 */
	public const STAGE_RESPONSE = "RESPONSE";

	private $stage;
	private $errorMessage;
	private $query;
	private $args;

	public function __construct(string $stage, string $errorMessage, string $query = null, array $args = null) {
		$this->stage        = $stage;
		$this->errorMessage = $errorMessage;
		$this->query        = $query;
		$this->args         = $args;

		parent::__construct("SQL $stage error: $errorMessage" . ($query === null ? "" : (", for query $query | " . json_encode($args))));
		$this->flattenTrace();
	}

	public function getStage() : string {
		return $this->stage;
	}

	public function getErrorMessage() : string {
		return $this->errorMessage;
	}

	public function getQuery() : ?string {
		return $this->query;
	}

	/**
	 * @return mixed[]|null
	 */
	public function getArgs() : ?array {
		return $this->args;
	}

	/**
	 * Flattens the trace such that the exception can be serialized
	 *
	 * @see https://gist.github.com/Thinkscape/805ba8b91cdce6bcaf7c Exception flattening solution by Artur Bodera
	 */
	protected function flattenTrace() : void {
		$traceProperty = (new ReflectionClass(Exception::class))->getProperty('trace');
		$traceProperty->setAccessible(true);
		$flatten = static function (&$value) {
			if($value instanceof Closure) {
				$closureReflection = new ReflectionFunction($value);
				$value             = sprintf(
					'(Closure at %s:%s)',
					$closureReflection->getFileName(),
					$closureReflection->getStartLine()
				);
			} elseif(is_object($value)) {
				$value = sprintf('object(%s)', get_class($value));
			} elseif(is_resource($value)) {
				$value = sprintf('resource(%s)', get_resource_type($value));
			}
		};
		do {
			$trace = $traceProperty->getValue($this);
			foreach($trace as &$call) {
				array_walk_recursive($call['args'], $flatten);
			}
			unset($call);
			$traceProperty->setValue($this, $trace);
		} while($this->getPrevious());
		$traceProperty->setAccessible(false);
	}

}
