<?php

declare(strict_types=1);

namespace synopsie\database\result;

class SqlColumnInfo {
	public const TYPE_STRING    = "string";
	public const TYPE_INT       = "int";
	public const TYPE_FLOAT     = "float";
	public const TYPE_TIMESTAMP = "timestamp";
	public const TYPE_BOOL      = "bool";
	public const TYPE_NULL      = "null";
	public const TYPE_OTHER     = "unknown";

	private string $name;
	private string $type;

	public function __construct(string $name, string $type) {
		$this->name = $name;
		$this->type = $type;
	}

	public function getName() : string {
		return $this->name;
	}

	public function getType() : string {
		return $this->type;
	}

}
