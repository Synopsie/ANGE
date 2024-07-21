<?php

declare(strict_types=1);

namespace synopsie\database\mysqli;

use synopsie\database\SqlError;
use Exception;
use JsonSerializable;
use mysqli;
use function mysqli_init;
use function mysqli_real_connect;
use function str_repeat;
use function strlen;

class MysqlCredentials implements JsonSerializable {
	private string $host;
	private string $username;
	private string $password;
	private string $schema;
	private int $port;
	private string $socket;

	/**
	 * @throws Exception
	 */
	public static function fromArray(array $array) : MysqlCredentials {
		if(!isset($array["schema"])) {
			throw new Exception("The attribute \"schema\" is missing in the MySQL settings");
		}
		return new MysqlCredentials(
			$array["host"],
			$array["username"],
			$array["password"],
			$array["schema"],
			$array["port"] ?? 3306,
			$array["socket"] ?? ""
		);
	}

	public function __construct(
		string $host,
		string $username,
		string $password,
		string $schema,
		int $port = 3306,
		string $socket = ""
	) {
		$this->host     = $host;
		$this->username = $username;
		$this->password = $password;
		$this->schema   = $schema;
		$this->port     = $port;
		$this->socket   = $socket;
	}

	public function newMysqli() : mysqli {
		$mysqli = mysqli_init();
		if($mysqli === false) {
			throw new SqlError(SqlError::STAGE_CONNECT, "Failed to initialize MySQLi");
		}
		@mysqli_real_connect($mysqli, $this->host, $this->username, $this->password, $this->schema, $this->port, $this->socket);
		if($mysqli->connect_error) {
			throw new SqlError(SqlError::STAGE_CONNECT, $mysqli->connect_error);
		}
		return $mysqli;
	}

	public function reconnectMysqli(mysqli $mysqli) : void {
		@mysqli_real_connect($mysqli, $this->host, $this->username, $this->password, $this->schema, $this->port, $this->socket);
		if($mysqli->connect_error) {
			throw new SqlError(SqlError::STAGE_CONNECT, $mysqli->connect_error);
		}
	}

	public function __toString() : string {
		return "$this->username@$this->host:$this->port/schema,$this->socket";
	}

	public function __debugInfo() {
		return [
			"host"     => $this->host,
			"username" => $this->username,
			"password" => str_repeat("*", strlen($this->password)),
			"schema"   => $this->schema,
			"port"     => $this->port,
			"socket"   => $this->socket
		];
	}

	public function jsonSerialize() : array {
		return [
			"host"     => $this->host,
			"username" => $this->username,
			"password" => $this->password,
			"schema"   => $this->schema,
			"port"     => $this->port,
			"socket"   => $this->socket
		];
	}

}
